<?php

declare(strict_types=1);

use VmEngine\Example\Support\RichTextSanitizer;

it('keeps formatting and links', function () {
    $html = RichTextSanitizer::clean('<h2>Title</h2><p>Hi <strong>there</strong> <a href="https://example.com" title="x">link</a></p><ul><li>one</li></ul>');

    expect($html)->toContain('<h2>Title</h2>')
        ->toContain('<strong>there</strong>')
        ->toContain('href="https://example.com"')
        ->toContain('<li>one</li>');
});

it('strips scripts, event handlers and javascript urls', function () {
    $html = RichTextSanitizer::clean('<p onclick="x()">Hi</p><script>alert(1)</script><a href="javascript:alert(1)">bad</a><img src="https://e.com/a.png" onerror="alert(1)">');

    expect($html)->toContain('<p>Hi</p>')
        ->not->toContain('script')
        ->not->toContain('onclick')
        ->not->toContain('onerror')
        ->not->toContain('javascript:');
});

it('returns null for empty or whitespace-only content', function () {
    expect(RichTextSanitizer::clean(''))->toBeNull()
        ->and(RichTextSanitizer::clean('<p> </p>'))->toBeNull();
});
