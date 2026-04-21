<?php
/**
 * ALG3PL Marketing Platform — Batch 4 Deploy Script
 * Ad Metrics: Meta Ads integration, widget, resource, migration
 *
 * USAGE: https://yourdomain.com/deploy_scale_batch4.php?token=ALG_DEPLOY_2026
 * DELETE THIS FILE after running.
 */

$DEPLOY_TOKEN = 'ALG_DEPLOY_2026';

// ── Auth ──────────────────────────────────────────────────────────────────────
if (($_GET['token'] ?? '') !== $DEPLOY_TOKEN) {
    http_response_code(403);
    die('<h1>403 Forbidden</h1>');
}

$log   = [];
$errors = [];

function w(string $path, string $content): void
{
    global $log, $errors;
    $dir = dirname($path);
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (file_put_contents($path, $content) === false) {
        $errors[] = "FAILED to write: $path";
    } else {
        $log[] = "OK: $path";
    }
}

function r(string $cmd): string
{
    global $log, $errors;
    $output = shell_exec($cmd . ' 2>&1');
    $log[]  = "CMD: $cmd\nOUT: " . trim($output ?? '');
    return $output ?? '';
}

// ── Resolve base path ─────────────────────────────────────────────────────────
$base = realpath(__DIR__ . '/..');

// =============================================================================
// FILE: Migration
// =============================================================================
w($base . '/database/migrations/2026_04_15_000004_create_ad_metrics_table.php', <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('platform'); // google | meta | linkedin
            $table->string('campaign_name');
            $table->date('period_start');
            $table->date('period_end');
            $table->bigInteger('impressions')->default(0);
            $table->bigInteger('clicks')->default(0);
            $table->decimal('spend', 10, 2)->default(0);
            $table->integer('leads_generated')->default(0);
            $table->decimal('cost_per_lead', 10, 2)->nullable();
            $table->decimal('roas', 8, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['country_id', 'platform', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_metrics');
    }
};
PHP);

// =============================================================================
// FILE: Model
// =============================================================================
w($base . '/app/Models/AdMetric.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdMetric extends Model
{
    protected $fillable = [
        'country_id', 'platform', 'campaign_name', 'period_start', 'period_end',
        'impressions', 'clicks', 'spend', 'leads_generated', 'cost_per_lead',
        'roas', 'notes', 'synced_at',
    ];

    protected $casts = [
        'spend'         => 'decimal:2',
        'cost_per_lead' => 'decimal:2',
        'roas'          => 'decimal:4',
        'period_start'  => 'date',
        'period_end'    => 'date',
        'synced_at'     => 'datetime',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function scopeByCountry(Builder $query, int $id): Builder
    {
        return $query->where('country_id', $id);
    }

    public function scopeByPlatform(Builder $query, string $platform): Builder
    {
        return $query->where('platform', $platform);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('period_start', '>=', now()->subDays($days)->toDateString());
    }

    public function getCtrAttribute(): float
    {
        if (! $this->impressions || $this->impressions == 0) {
            return 0.0;
        }
        return round(($this->clicks / $this->impressions) * 100, 2);
    }
}
PHP);

// =============================================================================
// FILE: MetaAdsService
// =============================================================================
if (! is_dir($base . '/app/Services/Ads')) {
    mkdir($base . '/app/Services/Ads', 0755, true);
}

w($base . '/app/Services/Ads/MetaAdsService.php', <<<'PHP'
<?php

namespace App\Services\Ads;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaAdsService
{
    private const API_BASE = 'https://graph.facebook.com/v18.0';
    private const TIMEOUT  = 15;

    public function getCampaignInsights(string $adAccountId = ''): array
    {
        $token     = config('services.meta.page_access_token', env('META_PAGE_ACCESS_TOKEN', ''));
        $accountId = $adAccountId ?: config('services.meta.ad_account_id', env('META_AD_ACCOUNT_ID', ''));

        if (empty($token) || empty($accountId)) {
            Log::debug('MetaAdsService: credentials missing, skipping sync.');
            return [];
        }

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->get(self::API_BASE . '/act_' . $accountId . '/insights', [
                    'access_token' => $token,
                    'fields'       => 'campaign_name,impressions,clicks,spend,date_start,date_stop',
                    'date_preset'  => 'last_30d',
                    'level'        => 'campaign',
                ]);

            if ($response->failed()) {
                Log::warning('MetaAdsService: API error', ['status' => $response->status()]);
                return [];
            }

            $data = $response->json('data', []);

            return array_map(fn ($row) => [
                'campaign_name' => $row['campaign_name'] ?? 'Unknown',
                'impressions'   => (int) ($row['impressions'] ?? 0),
                'clicks'        => (int) ($row['clicks'] ?? 0),
                'spend'         => (float) ($row['spend'] ?? 0),
                'date_start'    => $row['date_start'] ?? now()->subDays(30)->toDateString(),
                'date_stop'     => $row['date_stop'] ?? now()->toDateString(),
            ], $data);

        } catch (\Throwable $e) {
            Log::error('MetaAdsService: exception', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
PHP);

// =============================================================================
// FILE: SyncAdMetricsJob
// =============================================================================
w($base . '/app/Jobs/SyncAdMetricsJob.php', <<<'PHP'
<?php

namespace App\Jobs;

use App\Models\AdMetric;
use App\Models\Country;
use App\Services\Ads\MetaAdsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncAdMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(public readonly ?int $countryId = null) {}

    public function handle(MetaAdsService $metaService): void
    {
        Log::info('SyncAdMetricsJob: starting', ['country_id' => $this->countryId]);

        $campaigns = $metaService->getCampaignInsights();

        if (empty($campaigns)) {
            Log::info('SyncAdMetricsJob: no data returned.');
            return;
        }

        $countryId = $this->countryId ?? Country::where('is_active', true)->value('id');

        if (! $countryId) {
            Log::warning('SyncAdMetricsJob: no country_id, aborting.');
            return;
        }

        $synced = 0;

        foreach ($campaigns as $row) {
            $cpl = null;
            if (! empty($row['leads']) && $row['leads'] > 0 && $row['spend'] > 0) {
                $cpl = round($row['spend'] / $row['leads'], 2);
            }

            AdMetric::updateOrCreate(
                [
                    'country_id'    => $countryId,
                    'platform'      => 'meta',
                    'campaign_name' => $row['campaign_name'],
                    'period_start'  => $row['date_start'],
                    'period_end'    => $row['date_stop'],
                ],
                [
                    'impressions'     => $row['impressions'],
                    'clicks'          => $row['clicks'],
                    'spend'           => $row['spend'],
                    'leads_generated' => $row['leads'] ?? 0,
                    'cost_per_lead'   => $cpl,
                    'synced_at'       => now(),
                ]
            );
            $synced++;
        }

        Log::info('SyncAdMetricsJob: done.', ['records_synced' => $synced]);
    }

    public static function dispatchForCountry(?int $countryId = null): void
    {
        static::dispatch($countryId);
    }
}
PHP);

// =============================================================================
// FILE: AdMetricsWidget
// =============================================================================
w($base . '/app/Filament/Widgets/AdMetricsWidget.php', <<<'PHP'
<?php

namespace App\Filament\Widgets;

use App\Jobs\SyncAdMetricsJob;
use App\Models\AdMetric;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class AdMetricsWidget extends Widget
{
    protected static ?int $sort = 8;
    protected int|string|array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.ad-metrics';

    public ?string $countryFilter = '';

    public function mount(): void
    {
        $this->countryFilter = session('country_filter', '');
    }

    #[On('countryFilterUpdated')]
    public function onCountryFilterUpdated(string $countryFilter): void
    {
        $this->countryFilter = $countryFilter;
    }

    public function triggerSync(): void
    {
        SyncAdMetricsJob::dispatch($this->countryFilter ? (int) $this->countryFilter : null);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Sincronización iniciada.']);
    }

    public function getViewData(): array
    {
        $query = AdMetric::recent(30);
        if ($this->countryFilter) {
            $query->byCountry((int) $this->countryFilter);
        }
        $records = $query->get();

        $platforms  = ['google', 'meta', 'linkedin'];
        $byPlatform = [];

        foreach ($platforms as $platform) {
            $rows   = $records->where('platform', $platform);
            $spend  = $rows->sum('spend');
            $leads  = $rows->sum('leads_generated');
            $clicks = $rows->sum('clicks');
            $imps   = $rows->sum('impressions');

            $byPlatform[$platform] = [
                'spend'  => $spend,
                'leads'  => $leads,
                'cpl'    => ($leads > 0 && $spend > 0) ? round($spend / $leads, 2) : null,
                'ctr'    => ($imps > 0) ? round(($clicks / $imps) * 100, 2) : 0,
                'clicks' => $clicks,
                'imps'   => $imps,
                'count'  => $rows->count(),
            ];
        }

        $totals = ['spend' => $records->sum('spend'), 'leads' => $records->sum('leads_generated'), 'cpl' => null];
        if ($totals['leads'] > 0 && $totals['spend'] > 0) {
            $totals['cpl'] = round($totals['spend'] / $totals['leads'], 2);
        }

        $lastSync = $records->max('synced_at');

        return [
            'byPlatform' => $byPlatform,
            'totals'     => $totals,
            'hasData'    => $records->isNotEmpty(),
            'lastSync'   => $lastSync ? \Carbon\Carbon::parse($lastSync)->diffForHumans() : null,
        ];
    }
}
PHP);

// =============================================================================
// FILE: ad-metrics Blade view
// =============================================================================
w($base . '/resources/views/filament/widgets/ad-metrics.blade.php', <<<'BLADE'
<x-filament-widgets::widget>
<div style="background:#ffffff;border:1px solid #E2E5EA;border-radius:12px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div>
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#8B95A5;margin:0 0 2px;">Rendimiento Publicitario</p>
            <h3 style="font-size:18px;font-weight:700;color:#00243D;margin:0;">Inversión Publicitaria</h3>
            @if($lastSync)<p style="font-size:11px;color:#B8C0CC;margin:4px 0 0;">Última sync: {{ $lastSync }}</p>@endif
        </div>
        <button wire:click="triggerSync" style="display:inline-flex;align-items:center;gap:6px;background:#00243D;color:#ffffff;border:none;border-radius:8px;padding:8px 16px;font-size:13px;font-weight:600;cursor:pointer;" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
            Sync
        </button>
    </div>
    @if($hasData)
        <div style="background:#F7F8FA;border-radius:10px;padding:14px 20px;margin-bottom:20px;display:flex;gap:32px;flex-wrap:wrap;">
            <div><p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#8B95A5;margin:0 0 2px;">Gasto Total</p><p style="font-size:22px;font-weight:800;color:#00243D;margin:0;">${{ number_format($totals['spend'], 2) }}</p></div>
            <div><p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#8B95A5;margin:0 0 2px;">Leads Totales</p><p style="font-size:22px;font-weight:800;color:#00243D;margin:0;">{{ number_format($totals['leads']) }}</p></div>
            @if($totals['cpl'])<div><p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#8B95A5;margin:0 0 2px;">CPL Promedio</p><p style="font-size:22px;font-weight:800;color:#00243D;margin:0;">${{ number_format($totals['cpl'], 2) }}</p></div>@endif
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
            @php $g = $byPlatform['google']; @endphp
            <div style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:10px;padding:18px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;"><div style="width:32px;height:32px;background:#2563EB;border-radius:8px;display:flex;align-items:center;justify-content:center;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" style="width:16px;height:16px;"><path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/></svg></div><span style="font-size:13px;font-weight:700;color:#1E40AF;">Google Ads</span></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div><p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#60A5FA;margin:0 0 2px;">Gasto (USD)</p><p style="font-size:18px;font-weight:800;color:#1E40AF;margin:0;">${{ number_format($g['spend'], 2) }}</p></div>
                    <div><p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#60A5FA;margin:0 0 2px;">Leads</p><p style="font-size:18px;font-weight:800;color:#1E40AF;margin:0;">{{ number_format($g['leads']) }}</p></div>
                    <div><p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#60A5FA;margin:0 0 2px;">CPL (USD/lead)</p><p style="font-size:14px;font-weight:700;color:#2563EB;margin:0;">{{ $g['cpl'] ? '$'.number_format($g['cpl'],2) : '—' }}</p></div>
                    <div><p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#60A5FA;margin:0 0 2px;">CTR</p><p style="font-size:14px;font-weight:700;color:#2563EB;margin:0;">{{ $g['ctr'] }}%</p></div>
                </div>
            </div>
            @php $m = $byPlatform['meta']; @endphp
            <div style="background:#EEF2FF;border:1px solid #C7D2FE;border-radius:10px;padding:18px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;"><div style="width:32px;height:32px;background:#4F46E5;border-radius:8px;display:flex;align-items:center;justify-content:center;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" style="width:16px;height:16px;"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></div><span style="font-size:13px;font-weight:700;color:#4338CA;">Meta Ads</span></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div><p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#818CF8;margin:0 0 2px;">Gasto (USD)</p><p style="font-size:18px;font-weight:800;color:#4338CA;margin:0;">${{ number_format($m['spend'], 2) }}</p></div>
                    <div><p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#818CF8;margin:0 0 2px;">Leads</p><p style="font-size:18px;font-weight:800;color:#4338CA;margin:0;">{{ number_format($m['leads']) }}</p></div>
                    <div><p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#818CF8;margin:0 0 2px;">CPL (USD/lead)</p><p style="font-size:14px;font-weight:700;color:#4F46E5;margin:0;">{{ $m['cpl'] ? '$'.number_format($m['cpl'],2) : '—' }}</p></div>
                    <div><p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#818CF8;margin:0 0 2px;">CTR</p><p style="font-size:14px;font-weight:700;color:#4F46E5;margin:0;">{{ $m['ctr'] }}%</p></div>
                </div>
            </div>
            @php $l = $byPlatform['linkedin']; @endphp
            <div style="background:#F0F9FF;border:1px solid #BAE6FD;border-radius:10px;padding:18px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;"><div style="width:32px;height:32px;background:#0284C7;border-radius:8px;display:flex;align-items:center;justify-content:center;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" style="width:16px;height:16px;"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg></div><span style="font-size:13px;font-weight:700;color:#0369A1;">LinkedIn</span></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div><p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#38BDF8;margin:0 0 2px;">Gasto (USD)</p><p style="font-size:18px;font-weight:800;color:#0369A1;margin:0;">${{ number_format($l['spend'], 2) }}</p></div>
                    <div><p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#38BDF8;margin:0 0 2px;">Leads</p><p style="font-size:18px;font-weight:800;color:#0369A1;margin:0;">{{ number_format($l['leads']) }}</p></div>
                    <div><p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#38BDF8;margin:0 0 2px;">CPL (USD/lead)</p><p style="font-size:14px;font-weight:700;color:#0284C7;margin:0;">{{ $l['cpl'] ? '$'.number_format($l['cpl'],2) : '—' }}</p></div>
                    <div><p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#38BDF8;margin:0 0 2px;">CTR</p><p style="font-size:14px;font-weight:700;color:#0284C7;margin:0;">{{ $l['ctr'] }}%</p></div>
                </div>
            </div>
        </div>
    @else
        <div style="text-align:center;padding:40px 20px;">
            <p style="font-size:14px;font-weight:600;color:#00243D;margin:0 0 4px;">Sin datos — configura las credenciales</p>
            <p style="font-size:13px;color:#8B95A5;margin:0 0 16px;">Agrega META_PAGE_ACCESS_TOKEN y META_AD_ACCOUNT_ID al .env, o ingresa datos manualmente.</p>
        </div>
    @endif
</div>
</x-filament-widgets::widget>
BLADE);

// =============================================================================
// FILE: AdMetricResource
// =============================================================================
if (! is_dir($base . '/app/Filament/Resources/AdMetricResource/Pages')) {
    mkdir($base . '/app/Filament/Resources/AdMetricResource/Pages', 0755, true);
}

w($base . '/app/Filament/Resources/AdMetricResource.php', <<<'PHP'
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdMetricResource\Pages;
use App\Models\AdMetric;
use App\Models\Country;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdMetricResource extends Resource
{
    protected static ?string $model           = AdMetric::class;
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Analytics';
    protected static ?int    $navigationSort  = 5;
    protected static ?string $modelLabel      = 'Métrica Publicitaria';
    protected static ?string $pluralModelLabel = 'Métricas Publicitarias';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        $role = method_exists($user, 'hasRole') ? $user->hasRole(['admin', 'super_admin', 'manager']) : true;
        return (bool) $role;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identificación')->schema([
                Forms\Components\Select::make('country_id')->label('País')->options(Country::pluck('name', 'id'))->required()->searchable(),
                Forms\Components\Select::make('platform')->label('Plataforma')->options(['google' => 'Google Ads', 'meta' => 'Meta Ads', 'linkedin' => 'LinkedIn Ads'])->required(),
                Forms\Components\TextInput::make('campaign_name')->label('Nombre de Campaña')->required()->maxLength(255)->columnSpanFull(),
            ])->columns(2),
            Section::make('Período')->schema([
                Forms\Components\DatePicker::make('period_start')->label('Inicio')->required(),
                Forms\Components\DatePicker::make('period_end')->label('Fin')->required(),
            ])->columns(2),
            Section::make('Métricas')->schema([
                Forms\Components\TextInput::make('impressions')->label('Impresiones')->numeric()->default(0),
                Forms\Components\TextInput::make('clicks')->label('Clics')->numeric()->default(0),
                Forms\Components\TextInput::make('spend')->label('Gasto (USD)')->numeric()->prefix('$')->default(0),
                Forms\Components\TextInput::make('leads_generated')->label('Leads')->numeric()->default(0),
                Forms\Components\TextInput::make('cost_per_lead')->label('CPL (USD)')->numeric()->prefix('$')->nullable(),
                Forms\Components\TextInput::make('roas')->label('ROAS')->numeric()->nullable(),
            ])->columns(3),
            Section::make('Notas')->schema([
                Forms\Components\Textarea::make('notes')->label('Notas')->rows(3)->columnSpanFull(),
                Forms\Components\DateTimePicker::make('synced_at')->label('Última sincronización')->nullable(),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('country.name')->label('País')->badge()->sortable()->searchable(),
            Tables\Columns\TextColumn::make('platform')->label('Plataforma')->badge()
                ->color(fn (string $state): string => match ($state) { 'google' => 'info', 'meta' => 'warning', 'linkedin' => 'success', default => 'gray' })
                ->formatStateUsing(fn (string $state): string => match ($state) { 'google' => 'Google Ads', 'meta' => 'Meta Ads', 'linkedin' => 'LinkedIn', default => $state })->sortable(),
            Tables\Columns\TextColumn::make('campaign_name')->label('Campaña')->searchable()->limit(40),
            Tables\Columns\TextColumn::make('period_start')->label('Período')->formatStateUsing(fn ($record) => $record->period_start->format('d/m') . ' – ' . $record->period_end->format('d/m/Y'))->sortable(),
            Tables\Columns\TextColumn::make('spend')->label('Gasto')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('leads_generated')->label('Leads')->numeric()->sortable(),
            Tables\Columns\TextColumn::make('cost_per_lead')->label('CPL')->money('USD')->sortable()->placeholder('—'),
            Tables\Columns\TextColumn::make('synced_at')->label('Synced')->since()->sortable()->placeholder('Manual'),
        ])->filters([
            SelectFilter::make('country_id')->label('País')->options(Country::pluck('name', 'id')),
            SelectFilter::make('platform')->label('Plataforma')->options(['google' => 'Google Ads', 'meta' => 'Meta Ads', 'linkedin' => 'LinkedIn']),
        ])->defaultSort('period_start', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAdMetrics::route('/'),
            'create' => Pages\CreateAdMetric::route('/create'),
            'edit'   => Pages\EditAdMetric::route('/{record}/edit'),
        ];
    }
}
PHP);

w($base . '/app/Filament/Resources/AdMetricResource/Pages/ListAdMetrics.php', <<<'PHP'
<?php

namespace App\Filament\Resources\AdMetricResource\Pages;

use App\Filament\Resources\AdMetricResource;
use App\Jobs\SyncAdMetricsJob;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAdMetrics extends ListRecords
{
    protected static string $resource = AdMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync')->label('Sincronizar Meta Ads')->icon('heroicon-o-arrow-path')->color('gray')
                ->action(function () {
                    SyncAdMetricsJob::dispatch();
                    Notification::make()->title('Sincronización iniciada')->success()->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
PHP);

w($base . '/app/Filament/Resources/AdMetricResource/Pages/CreateAdMetric.php', <<<'PHP'
<?php

namespace App\Filament\Resources\AdMetricResource\Pages;

use App\Filament\Resources\AdMetricResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdMetric extends CreateRecord
{
    protected static string $resource = AdMetricResource::class;
}
PHP);

w($base . '/app/Filament/Resources/AdMetricResource/Pages/EditAdMetric.php', <<<'PHP'
<?php

namespace App\Filament\Resources\AdMetricResource\Pages;

use App\Filament\Resources\AdMetricResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdMetric extends EditRecord
{
    protected static string $resource = AdMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
PHP);

// =============================================================================
// Dashboard patch: add AdMetricsWidget to getContentWidgets
// =============================================================================
$dashPath = $base . '/app/Filament/Pages/Dashboard.php';
$dash = file_get_contents($dashPath);
$needsImport = strpos($dash, 'AdMetricsWidget') === false;

if ($needsImport) {
    $dash = str_replace(
        'use App\Filament\Widgets\ContactTimelineWidget;',
        "use App\Filament\Widgets\AdMetricsWidget;\nuse App\Filament\Widgets\ContactTimelineWidget;",
        $dash
    );
    $dash = str_replace(
        'ContactTimelineWidget::class,',
        "ContactTimelineWidget::class,\n            AdMetricsWidget::class,",
        $dash
    );
    file_put_contents($dashPath, $dash);
    $log[] = 'OK: Dashboard.php patched with AdMetricsWidget';
} else {
    $log[] = 'SKIP: Dashboard.php already has AdMetricsWidget';
}

// =============================================================================
// Run migration
// =============================================================================
$php = PHP_BINARY ?: 'php';
$artisan = $base . '/artisan';

$out = r("{$php} {$artisan} migrate --force --path=database/migrations/2026_04_15_000004_create_ad_metrics_table.php 2>&1");
if (str_contains($out, 'error') || str_contains($out, 'Error')) {
    $errors[] = 'Migration may have failed. Output: ' . $out;
}

// =============================================================================
// Clear caches
// =============================================================================
r("{$php} {$artisan} config:clear 2>&1");
r("{$php} {$artisan} route:clear 2>&1");
r("{$php} {$artisan} view:clear 2>&1");
r("{$php} {$artisan} cache:clear 2>&1");
r("{$php} {$artisan} optimize 2>&1");

$log[] = 'Caches cleared and optimized.';

// =============================================================================
// Self-delete
// =============================================================================
$selfDelete = true;

// ── Output ────────────────────────────────────────────────────────────────────
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Batch 4 Deploy</title>
<style>body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:32px;max-width:900px;margin:0 auto;} h1{color:#38bdf8;} .ok{color:#4ade80;} .err{color:#f87171;} pre{background:#1e293b;padding:16px;border-radius:8px;overflow-x:auto;font-size:12px;}</style>
</head>
<body>
<h1>ALG3PL Batch 4 — Ad Metrics Deploy</h1>
<?php if ($errors): ?>
<h2 class="err">Errors (<?= count($errors) ?>)</h2>
<pre><?= implode("\n", array_map('htmlspecialchars', $errors)) ?></pre>
<?php else: ?>
<p class="ok">All files written successfully. No errors.</p>
<?php endif; ?>
<h2>Log</h2>
<pre><?= implode("\n", array_map('htmlspecialchars', $log)) ?></pre>
<p style="margin-top:24px;color:#64748b;">Script will self-delete now. Delete manually if it persists.</p>
</body>
</html>
<?php

// Self-delete at the very end
if ($selfDelete) {
    @unlink(__FILE__);
}
