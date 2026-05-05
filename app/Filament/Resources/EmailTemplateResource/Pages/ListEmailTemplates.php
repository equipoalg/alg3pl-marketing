<?php

namespace App\Filament\Resources\EmailTemplateResource\Pages;

use App\Filament\Resources\EmailTemplateResource;
use App\Models\EmailTemplate;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;
use Livewire\Attributes\Url;

/**
 * Custom CRM-style ListEmailTemplates. Mismo patrón que el resto.
 * Slide-over incluye preview HTML rendered y toggle is_active.
 */
class ListEmailTemplates extends Page
{
    protected static string $resource = EmailTemplateResource::class;
    protected string $view = 'filament.resources.email-template-resource.pages.email-templates-hub';
    protected Width|string|null $maxContentWidth = Width::Full;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'category')]
    public string $categoryFilter = '';

    #[Url(as: 'active')]
    public string $activeFilter = '';

    #[Url(as: 'sort')]
    public string $sortBy = 'recent';

    #[Url(as: 'selected')]
    public ?int $selectedId = null;

    public array $selectedIds = [];

    public string $editName = '';
    public ?string $editCategory = null;
    public ?string $editSubject = null;
    public ?string $editBodyHtml = null;
    public bool $editIsActive = true;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable { return ''; }
    public function getTitle(): string { return 'Email Templates'; }
    protected function getHeaderActions(): array { return []; }

    public function mount(): void { if ($this->selectedId) $this->hydrateEditForm(); }

    public function setCategoryFilter(string $value): void
    {
        if (in_array($value, ['', 'welcome', 'follow_up', 'nurturing', 'quote', 'newsletter', 'notification', 'custom'], true)) {
            $this->categoryFilter = $value;
        }
    }
    public function setActiveFilter(string $value): void
    {
        if (in_array($value, ['', 'yes', 'no'], true)) $this->activeFilter = $value;
    }
    public function setSortBy(string $value): void
    {
        if (in_array($value, ['recent', 'name', 'usage_desc'], true)) $this->sortBy = $value;
    }
    public function clearFilters(): void
    {
        $this->search = ''; $this->categoryFilter = ''; $this->activeFilter = ''; $this->sortBy = 'recent';
    }

    public function selectTemplate(int $id): void { $this->selectedId = $id; $this->hydrateEditForm(); }
    public function closeTemplate(): void { $this->selectedId = null; }
    public function updatedSelectedId($v): void { if ($v) $this->hydrateEditForm(); }
    public function hydrateEditForm(): void
    {
        if (! $this->selectedId) return;
        $t = EmailTemplateResource::getEloquentQuery()->find($this->selectedId);
        if (! $t) return;
        $this->editName     = (string) $t->name;
        $this->editCategory = $t->category;
        $this->editSubject  = $t->subject;
        $this->editBodyHtml = $t->body_html;
        $this->editIsActive = (bool) $t->is_active;
    }
    public function saveTemplate(): void
    {
        if (! $this->selectedId) return;
        $t = EmailTemplateResource::getEloquentQuery()->find($this->selectedId);
        if (! $t) return;
        $t->update([
            'name'      => trim($this->editName) ?: $t->name,
            'category'  => in_array($this->editCategory, ['welcome','follow_up','nurturing','quote','newsletter','notification','custom'], true) ? $this->editCategory : $t->category,
            'subject'   => $this->editSubject,
            'body_html' => $this->editBodyHtml,
            'is_active' => (bool) $this->editIsActive,
        ]);
        Notification::make()->title('Template guardado')->success()->send();
    }

    public function toggleActive(int $id): void
    {
        $t = EmailTemplateResource::getEloquentQuery()->find($id);
        if (! $t) return;
        $t->update(['is_active' => ! $t->is_active]);
    }

    public function toggleSelected(int $id): void
    {
        if (in_array($id, $this->selectedIds, true)) {
            $this->selectedIds = array_values(array_filter($this->selectedIds, fn ($i) => $i !== $id));
        } else { $this->selectedIds[] = $id; }
    }
    public function clearSelectedTemplates(): void { $this->selectedIds = []; }
    public function selectAllVisible(): void
    {
        $this->selectedIds = $this->buildQuery()->limit(500)->pluck('id')->all();
    }
    public function bulkActivate(): void { $this->bulkSetActive(true); }
    public function bulkDeactivate(): void { $this->bulkSetActive(false); }
    private function bulkSetActive(bool $value): void
    {
        if (empty($this->selectedIds)) return;
        EmailTemplateResource::getEloquentQuery()->whereIn('id', $this->selectedIds)->update(['is_active' => $value]);
        $count = count($this->selectedIds);
        $verb = $value ? 'activados' : 'desactivados';
        $this->selectedIds = [];
        Notification::make()->title("$count templates $verb")->success()->send();
    }

    public static function categoryIcon(?string $cat): array
    {
        return match ($cat) {
            'welcome'      => ['icon' => '👋', 'bg' => '#FEF3C7', 'fg' => '#92400E', 'label' => 'Welcome'],
            'follow_up'    => ['icon' => '↻',  'bg' => '#DBEAFE', 'fg' => '#1E3A8A', 'label' => 'Follow up'],
            'nurturing'    => ['icon' => '💧', 'bg' => '#D1FAE5', 'fg' => '#065F46', 'label' => 'Nurturing'],
            'quote'        => ['icon' => '$',  'bg' => '#FCE7F3', 'fg' => '#9D174D', 'label' => 'Quote'],
            'newsletter'   => ['icon' => '📰', 'bg' => '#EDE9FE', 'fg' => '#5B21B6', 'label' => 'Newsletter'],
            'notification' => ['icon' => '🔔', 'bg' => '#FEE2E2', 'fg' => '#9F1239', 'label' => 'Notification'],
            'custom'       => ['icon' => '⚙',  'bg' => '#F1F5F9', 'fg' => '#334155', 'label' => 'Custom'],
            default        => ['icon' => '?',  'bg' => 'var(--alg-surface-2)', 'fg' => 'var(--alg-ink-4)', 'label' => (string) $cat],
        };
    }

    protected function buildQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = EmailTemplateResource::getEloquentQuery();

        if ($this->search !== '') {
            $like = '%' . $this->search . '%';
            $q->where(function ($x) use ($like) {
                $x->where('name', 'like', $like)->orWhere('subject', 'like', $like);
            });
        }
        if ($this->categoryFilter !== '') $q->where('category', $this->categoryFilter);
        if ($this->activeFilter === 'yes') $q->where('is_active', true);
        if ($this->activeFilter === 'no') $q->where('is_active', false);

        return match ($this->sortBy) {
            'name'        => $q->orderBy('name'),
            'usage_desc'  => $q->orderByDesc('usage_count')->orderBy('name'),
            default       => $q->orderByDesc('updated_at'),
        };
    }

    public function getViewData(): array
    {
        $templates = $this->buildQuery()->with('country')->limit(500)->get();

        $allQ = EmailTemplateResource::getEloquentQuery();
        $kpis = [
            'total'    => (clone $allQ)->count(),
            'active'   => (clone $allQ)->where('is_active', true)->count(),
            'inactive' => (clone $allQ)->where('is_active', false)->count(),
            'usage'    => (int) (clone $allQ)->sum('usage_count'),
        ];

        $selected = null;
        if ($this->selectedId) {
            $selected = EmailTemplateResource::getEloquentQuery()->with('country')->find($this->selectedId);
        }

        return [
            'templates'       => $templates,
            'totalShown'      => $templates->count(),
            'kpis'            => $kpis,
            'selected'        => $selected,
            'selectedIds'     => $this->selectedIds,
            'currentSearch'   => $this->search,
            'currentCategory' => $this->categoryFilter,
            'currentActive'   => $this->activeFilter,
            'currentSort'     => $this->sortBy,
        ];
    }
}
