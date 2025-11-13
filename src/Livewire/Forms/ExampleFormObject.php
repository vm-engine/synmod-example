<?php

namespace VmEngine\Example\Livewire\Forms;

use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;
use Livewire\Form;
use Livewire\WithFileUploads;
use VmEngine\Example\Models\Example;

class ExampleFormObject extends Form
{
    use WithFileUploads;

    public ?Example $example = null;

    public ?int $category_id = null;

    public string $text = '';

    public string $textarea = '';

    public string $email = '';

    public string $protected = '';

    public int $number = 0;

    public string $masked = '0';

    public string $dropdown = '0';

    public array $multidropdown = [];

    public string $radio = '0';

    public array $checkbox = [];

    public string $date = '';

    public string $datetime = '';

    public $file;

    public string $color = '';

    public function setExample(Example $example): void
    {
        $this->example = $example;

        $this->category_id = $example->category_id;
        $this->text = $example->text;
        $this->textarea = $example->textarea;
        $this->email = $example->email;
        $this->protected = $example->protected;
        $this->number = $example->number;
        $this->masked = number_format((int) $example->masked, 0, ',', '.');
        $this->dropdown = (string) $example->dropdown;
        $this->multidropdown = $example->multidropdown;
        $this->radio = (string) $example->radio;
        $this->checkbox = $example->checkbox;
        $this->date = $example->date;
        $this->datetime = $example->datetime->format('Y-m-d H:i');
        $this->color = $example->color;
    }

    public function rules()
    {
        return [
            'text' => 'required',
            'email' => 'required|email',
            'protected' => [
                'required',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'number' => 'required|numeric|min:1',
            // 'multidropdown' => 'required|array|min:2',
            'checkbox' => 'required|array|min:1',
            'date' => 'date',
            'datetime' => 'date',
            'file' => [
                'nullable',
                'sometimes',
                File::types(['doc', 'docx', 'pdf', 'xls', 'xlsx', 'ppt', 'pptx'])
                    ->max(1024),
            ],
            'color' => 'string',
        ];
    }

    public function store(): void
    {
        $this->validate();

        if ($this->file) {
            $file = $this->file->storePublicly('examples');
        }

        Example::create($this->only([
            'category_id',
            'text',
            'textarea',
            'email',
            'protected',
            'number',
            'masked',
            'dropdown',
            'multidropdown',
            'radio',
            'checkbox',
            'date',
            'datetime',
            'color',
        ]) + ['file' => $file ?? '']);

        $this->reset();
    }

    public function update(): void
    {
        $this->validate();

        $this->example->update($this->only([
            'category_id',
            'text',
            'textarea',
            'email',
            'protected',
            'number',
            'masked',
            'dropdown',
            'multidropdown',
            'radio',
            'checkbox',
            'date',
            'datetime',
            'file',
            'color',
        ]));
    }
}
