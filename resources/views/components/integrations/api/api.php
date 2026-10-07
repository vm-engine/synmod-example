<?php

declare(strict_types=1);

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use VmEngine\Example\Livewire\Concerns\IntegrationPatternPage;

new class extends Component
{
    use IntegrationPatternPage;

    public function title(): string
    {
        return __('example::integrations.api');
    }

    /**
     * The registered v1 routes, read from the router so the page never drifts.
     *
     * @return list<array{method: string, uri: string, permission: string, signed: bool}>
     */
    #[Computed()]
    public function endpoints(): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->filter(fn (RoutingRoute $route): bool => str_starts_with((string) $route->getName(), 'api.v1.example.'))
            ->map(function (RoutingRoute $route): array {
                $middleware = $route->gatherMiddleware();
                $permission = collect($middleware)->first(fn ($m): bool => is_string($m) && str_starts_with($m, 'api.permission:'));

                return [
                    'method' => implode('|', array_diff($route->methods(), ['HEAD'])),
                    'uri' => '/'.$route->uri(),
                    'permission' => $permission ? substr($permission, strlen('api.permission:')) : '—',
                    'signed' => in_array('api.signed', $middleware, true),
                ];
            })
            ->sortBy('uri')
            ->values()
            ->all();
    }

    /**
     * MFC classes are compiled into storage/, so resolve the file from the installed package.
     */
    public function downloadPostman(): BinaryFileResponse
    {
        return response()->download(base_path('vendor/vm-engine/synmod-example/docs/postman/example-api-v1.postman_collection.json'), 'example-api-v1.postman_collection.json', ['Content-Type' => 'application/json']);
    }

    /**
     * @return array<string, string>
     */
    #[Computed()]
    public function snippets(): array
    {
        $base = rtrim((string) config('app.url'), '/');

        return [
            'list' => "curl -H 'Accept: application/json' -H 'Authorization: Bearer <access_token>' '{$base}/api/v1/example/examples?status=published'",
            'sign' => "body='{\"text\":\"Hi\",\"email\":\"a@b.c\",\"status\":\"draft\"}'\nts=\$(date -u +%Y-%m-%dT%H:%M:%SZ)\nsig=\$(printf 'POST\\n/api/v1/example/examples\\n%s\\n%s' \"\$ts\" \"\$(printf %s \"\$body\" | sha256sum | cut -d' ' -f1)\" | openssl dgst -sha256 -hmac \"\$SECRET\" -binary | base64)\ncurl -X POST -H 'Content-Type: application/json' -H 'Accept: application/json' -H 'Authorization: Bearer <access_token>' -H \"X-Timestamp: \$ts\" -H \"X-Signature: \$sig\" -d \"\$body\" '{$base}/api/v1/example/examples'",
        ];
    }
};
