<?php

declare(strict_types=1);

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Livewire\Concerns\FrontendPage;
use VmEngine\Example\Models\Example;

new class extends Component
{
    use FrontendPage;
    use WithPagination;

    #[Url()]
    public string $q = '';

    public function pageTitle(): string
    {
        return __('example::frontend.search');
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    #[Computed()]
    public function term(): string
    {
        return mb_substr(trim($this->q), 0, 100);
    }

    /**
     * Title or content LIKE the term; % and _ are escaped so they match literally.
     *
     * @return LengthAwarePaginator<int, Example>|null
     */
    #[Computed()]
    public function results(): ?LengthAwarePaginator
    {
        if ($this->term === '') {
            return null;
        }

        $like = '%'.addcslashes($this->term, '\\%_').'%';

        return Example::query()->published()
            ->where(fn ($query) => $query->whereRaw("text like ? escape '\\'", [$like])->orWhereRaw("content like ? escape '\\'", [$like]))
            ->latest('id')
            ->paginate(10);
    }

    /**
     * Escaped text with the term wrapped in <mark>.
     */
    public function highlight(string $text): HtmlString
    {
        $escaped = e($text);

        if ($this->term === '') {
            return new HtmlString($escaped);
        }

        return new HtmlString((string) preg_replace('/('.preg_quote(e($this->term), '/').')/iu', '<mark>$1</mark>', $escaped));
    }
};
