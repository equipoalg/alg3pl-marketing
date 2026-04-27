<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Mixin for Filament Resources whose underlying model has a `country_id`
 * column. Automatically scopes ALL queries (table list, sidebar count badge,
 * global search, exports) by the country selected in the sidebar country
 * switcher (`session('country_filter')`).
 *
 * When no country is selected (Global view), every record is shown — same
 * behavior as before. When a country is selected, only records matching
 * that country_id appear.
 *
 * Usage:
 *   class LeadResource extends Resource {
 *       use \App\Filament\Concerns\ScopesByCountryFilter;
 *       …
 *   }
 *
 * The trait deliberately does NOT touch `getEloquentQuery` if the parent's
 * version is doing more than just calling `parent::query()` — it composes
 * via parent::getEloquentQuery(), so any existing soft-delete scopes,
 * tenant filters, or eager-loads are preserved.
 */
trait ScopesByCountryFilter
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if ($countryId = session('country_filter')) {
            $query->where('country_id', (int) $countryId);
        }

        return $query;
    }
}
