<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders a wire-ignored Jodit host bound to the given field', function () {
    $html = Blade::render('<x-example::rich-text field="form.content" value="<p>Hi</p>" :height="320" />');

    expect($html)->toContain('wire:ignore')
        ->toContain('exampleRichText(')
        ->toContain('&quot;field&quot;:&quot;form.content&quot;')
        ->toContain('&quot;height&quot;:320')
        ->toContain('&lt;p&gt;Hi&lt;/p&gt;');
});
