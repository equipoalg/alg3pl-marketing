<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource;
use App\Models\Tag;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;
use Livewire\Attributes\Url;

/**
 * Custom CRM-style ListTags. Tags con color preview + usage counts.
 */
class ListTags extends Page
{
    protected static string $resource = TagResource::class;
    protected string $view = 'filament.resources.tag-resource.pages.tags-hub';
    protected Width|string|null $maxContentWidth = Width::Full;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'sort')]
    public string $sortBy = 'name';

    #[Url(as: 'selected')]
    public ?int $selectedId = null;

    public string $editName = '';
    public ?string $editSlug = null;
    public ?string $editColor = null;
    public ?string $editDescription = null;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable { return ''; }
    public function getTitle(): string { return 'Tags'; }
    protected function getHeaderActions(): array { return []; }

    public function mount(): void { if ($this->selectedId) $this->hydrateEditForm(); }

    public function setSortBy(string $value): void
    {
        if (in_array($value, ['name', 'usage_desc', 'recent'], true)) $this->sortBy = $value;
    }
    public function clearFilters(): void
    {
        $this->search = ''; $this->sortBy = 'name';
    }

    public function selectTag(int $id): void { $this->selectedId = $id; $this->hydrateEditForm(); }
    public function closeTag(): void { $this->selectedId = null; }
    public function updatedSelectedId($v): void { if ($v) $this->hydrateEditForm(); }

    public function hydrateEditForm(): void
    {
        if (! $this->selectedId) return;
        $t = Tag::find($this->selectedId);
        if (! $t) return;
        $this->editName        = (string) $t->name;
        $this->editSlug        = $t->slug;
        $this->editColor       = $t->color ?? '#6366F1';
        $this->editDescription = $t->description;
    }

    public function saveTag(): void
    {
        if (! $this->selectedId) return;
        $t = Tag::find($this->selectedId);
        if (! $t) return;
        $t->update([
            'name'        => trim($this->editName) ?: $t->name,
            'slug'        => $this->editSlug ?: $t->slug,
            'color'       => $this->editColor ?: '#6366F1',
            'description' => $this->editDescription,
        ]);
        Notification::make()->title('Tag guardado')->success()->send();
    }

    protected function buildQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = Tag::query()->withCount(['leads']);

        if ($this->search !== '') {
            $like = '%' . $this->search . '%';
            $q->where(function ($x) use ($like) {
                $x->where('name', 'like', $like)->orWhere('slug', 'like', $like)->orWhere('description', 'like', $like);
            });
        }

        return match ($this->sortBy) {
            'usage_desc' => $q->orderByDesc('leads_count')->orderBy('name'),
            'recent'     => $q->orderByDesc('created_at'),
            default      => $q->orderBy('name'),
        };
    }

    public function getViewData(): array
    {
        $tags = $this->buildQuery()->limit(500)->get();

        $kpis = [
            'total'      => Tag::count(),
            'used'       => Tag::has('leads')->count(),
            'unused'     => Tag::doesntHave('leads')->count(),
            'totalLeads' => $tags->sum('leads_count'),
        ];

        $selected = null;
        if ($this->selectedId) {
            $selected = Tag::withCount(['leads'])->find($this->selectedId);
        }

        return [
            'tags'          => $tags,
            'totalShown'    => $tags->count(),
            'kpis'          => $kpis,
            'selected'      => $selected,
            'currentSearch' => $this->search,
            'currentSort'   => $this->sortBy,
        ];
    }
}
