<?php

declare(strict_types=1);

namespace VmEngine\Example\Support;

/**
 * Server-side HTML allowlist for rich-text fields (HTMLPurifier via mews/purifier).
 * Keeps basic formatting, lists, links and images; drops scripts, event
 * handlers, inline styles and non-http(s)/mailto URLs.
 */
final class RichTextSanitizer
{
    /** @var array<string, mixed> */
    private const CONFIG = [
        'HTML.Allowed' => 'p,br,strong,b,em,i,u,s,h2,h3,h4,ul,ol,li,blockquote,code,pre,a[href|title|target],img[src|alt|width|height]',
        'URI.AllowedSchemes' => ['http' => true, 'https' => true, 'mailto' => true],
        'Attr.AllowedFrameTargets' => ['_blank'],
        'AutoFormat.RemoveEmpty' => true,
        'AutoFormat.RemoveEmpty.RemoveNbsp' => true,
    ];

    public static function clean(string $html): ?string
    {
        $clean = trim((string) clean($html, self::CONFIG));

        return trim(strip_tags($clean)) === '' && ! str_contains($clean, '<img') ? null : $clean;
    }
}
