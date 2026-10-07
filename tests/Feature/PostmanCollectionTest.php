<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('ships a valid Postman collection whose requests all hit real routes', function () {
    $collection = json_decode((string) file_get_contents(__DIR__.'/../../docs/postman/example-api-v1.postman_collection.json'), true, flags: JSON_THROW_ON_ERROR);

    expect($collection['info']['schema'])->toContain('v2.1.0');

    $requests = collect($collection['item'])->flatMap(fn (array $item): array => $item['item'] ?? [$item]);
    // Compare with variable segments as "*": {id} in routes, {{var}} / :var in Postman.
    $routes = collect(Route::getRoutes()->getRoutes())
        ->flatMap(fn ($route): array => array_map(fn (string $method): string => $method.' /'.preg_replace('/\{[^}]+\}/', '*', ltrim($route->uri(), '/')), $route->methods()))
        ->all();

    foreach ($requests as $request) {
        $segments = array_map(fn (string $segment): string => str_starts_with($segment, '{{') || str_starts_with($segment, ':') ? '*' : $segment, $request['request']['url']['path']);
        $signature = $request['request']['method'].' /'.implode('/', $segments);

        expect(in_array($signature, $routes, true))->toBeTrue("No route for {$signature}");
    }

    expect($requests->firstWhere('name', 'Create example (signed)')['event'][0]['script']['exec'] ?? [])->not->toBeEmpty();
});
