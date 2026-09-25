<?php

namespace VmEngine\Example\Livewire\Forms;

use Livewire\Form;
use VmEngine\Example\Models\ExampleCategory;

class CategoryFormObject extends Form
{
    public ?ExampleCategory $category = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public bool $is_active = true;

    public function setCategory(ExampleCategory $category): void
    {
        $this->category = $category;

        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description ?? '';
        $this->is_active = $category->is_active;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $categoryId = $this->category?->id;

        return [
            'name' => 'required|string|max:255',
            'slug' => "required|string|max:255|unique:example_categories,slug,{$categoryId}",
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function store(): void
    {
        $this->validate();

        ExampleCategory::create($this->only([
            'name',
            'slug',
            'description',
            'is_active',
        ]));

        $this->reset();
    }

    public function update(): void
    {
        $this->validate();

        $this->category->update($this->only([
            'name',
            'slug',
            'description',
            'is_active',
        ]));
    }
}
