<?php

namespace App\Filament\Imports;

use App\Models\Country;
use App\Models\Lead;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class LeadImporter extends Importer
{
    protected static ?string $model = Lead::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:191']),
            ImportColumn::make('email')
                ->rules(['nullable', 'email', 'max:191']),
            ImportColumn::make('phone')
                ->rules(['nullable', 'max:50']),
            ImportColumn::make('company')
                ->rules(['nullable', 'max:191']),
            ImportColumn::make('country_id')
                ->label('Country')
                ->guess(['country', 'pais', 'country_code'])
                ->fillRecordUsing(function (Lead $record, string $state): void {
                    // Support country codes (SV, GT, etc.) or IDs
                    $country = Country::where('code', strtolower($state))
                        ->orWhere('id', $state)
                        ->orWhere('name', $state)
                        ->first();
                    $record->country_id = $country?->id;
                }),
            ImportColumn::make('service_interest')
                ->guess(['service', 'servicio', 'interest']),
            ImportColumn::make('source')
                ->guess(['source', 'fuente', 'origin'])
                ->fillRecordUsing(function (Lead $record, string $state): void {
                    $valid = ['organic', 'paid', 'social', 'referral', 'direct', 'email', 'whatsapp', 'other'];
                    $record->source = in_array(strtolower($state), $valid) ? strtolower($state) : 'other';
                }),
            ImportColumn::make('status')
                ->guess(['status', 'estado'])
                ->fillRecordUsing(function (Lead $record, string $state): void {
                    $valid = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];
                    $record->status = in_array(strtolower($state), $valid) ? strtolower($state) : 'new';
                }),
            ImportColumn::make('notes')
                ->guess(['notes', 'notas', 'comments', 'message', 'mensaje']),
            ImportColumn::make('utm_source'),
            ImportColumn::make('utm_medium'),
            ImportColumn::make('utm_campaign'),
            ImportColumn::make('landing_page')
                ->guess(['page', 'landing', 'url']),
            ImportColumn::make('estimated_value')
                ->guess(['value', 'valor'])
                ->numeric(),
        ];
    }

    public function resolveRecord(): ?Lead
    {
        // Deduplicate by email if exists
        if ($this->data['email'] ?? null) {
            return Lead::firstOrNew(['email' => $this->data['email']]);
        }

        return new Lead();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Lead import completed: ' . number_format($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' failed.';
        }

        return $body;
    }
}
