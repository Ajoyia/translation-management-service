<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTranslationRequest;
use App\Http\Requests\UpdateTranslationRequest;
use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TranslationController extends Controller
{
    public function __construct(
        private readonly TranslationService $translationService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'locale' => $request->input('locale'),
            'key' => $request->input('key'),
            'search' => $request->input('search'),
            'tags' => $request->has('tags')
                ? (is_array($request->tags) ? $request->tags : explode(',', $request->tags))
                : null,
        ];

        $filters = array_filter($filters, fn ($value) => $value !== null);
        $perPage = (int) $request->input('per_page', 15);

        $translations = $this->translationService->getAll($filters, $perPage);

        return response()->json($translations);
    }

    public function store(StoreTranslationRequest $request): JsonResponse
    {
        $translation = $this->translationService->create($request->validated());

        return response()->json([
            'message' => 'Translation created successfully',
            'data' => $translation,
        ], 201);
    }

    public function show(Translation $translation): JsonResponse
    {
        $translation->load('tags');

        return response()->json([
            'data' => $translation,
        ]);
    }

    public function update(UpdateTranslationRequest $request, Translation $translation): JsonResponse
    {
        $translation = $this->translationService->update($translation, $request->validated());

        return response()->json([
            'message' => 'Translation updated successfully',
            'data' => $translation,
        ]);
    }

    public function destroy(Translation $translation): JsonResponse
    {
        $this->translationService->delete($translation);

        return response()->json([
            'message' => 'Translation deleted successfully',
        ]);
    }
}
