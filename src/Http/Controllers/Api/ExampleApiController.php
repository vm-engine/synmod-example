<?php

declare(strict_types=1);

namespace VmEngine\Example\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Http\Requests\StoreExampleRequest;
use VmEngine\Example\Http\Requests\UpdateExampleStatusRequest;
use VmEngine\Example\Http\Resources\ExampleCategoryResource;
use VmEngine\Example\Http\Resources\ExampleResource;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;

/**
 * Example module API v1 (routes/api.v1.php). Permissions are enforced by the
 * route middleware; this class validates input and shapes output.
 */
class ExampleApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(ExampleStatus::class)],
            'category_id' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'between:1,50'],
        ]);

        $examples = Example::query()
            ->with(['category', 'tags'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['category_id'] ?? null, fn ($query, int|string $id) => $query->where('category_id', (int) $id))
            ->when(isset($filters['q']) && trim($filters['q']) !== '', fn ($query) => $query->search(trim($filters['q'])))
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();

        return ExampleResource::collection($examples);
    }

    public function show(int $id): ExampleResource
    {
        return new ExampleResource(Example::query()->with(['category', 'tags'])->findOrFail($id));
    }

    public function categories(): AnonymousResourceCollection
    {
        return ExampleCategoryResource::collection(ExampleCategory::query()->orderBy('name')->get());
    }

    public function updateStatus(UpdateExampleStatusRequest $request, int $id): ExampleResource
    {
        $example = Example::query()->findOrFail($id);
        $data = $request->validated();

        if ($data['status'] === ExampleStatus::Published->value) {
            $example->meta = [...($example->meta ?? []), 'publish_note' => trim((string) $data['publish_note'])];
        }

        $example->status = ExampleStatus::from($data['status']);
        $example->save();

        return new ExampleResource($example->load(['category', 'tags']));
    }

    public function store(StoreExampleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $note = $data['publish_note'] ?? null;
        unset($data['publish_note']);

        $example = Example::query()->create([
            ...$data,
            'meta' => $data['status'] === ExampleStatus::Published->value ? ['publish_note' => trim((string) $note)] : null,
        ]);

        return (new ExampleResource($example->load(['category', 'tags'])))->response()->setStatusCode(201);
    }
}
