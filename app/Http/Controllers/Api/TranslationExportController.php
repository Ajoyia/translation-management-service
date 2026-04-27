<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TranslationExportController extends Controller
{
    public function __construct(
        private readonly TranslationService $translationService
    ) {
    }

    public function export(Request $request): JsonResponse
    {
        $locale = $request->input('locale', 'en');
        $tags = $request->has('tags')
            ? (is_array($request->tags) ? $request->tags : explode(',', $request->tags))
            : [];

        $translations = $this->translationService->exportByLocale($locale, $tags);

        return response()->json([
            'locale' => $locale,
            'translations' => $translations,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function exportAll(Request $request): JsonResponse
    {
        $tags = $request->has('tags')
            ? (is_array($request->tags) ? $request->tags : explode(',', $request->tags))
            : [];

        $data = $this->translationService->exportAll($tags);

        return response()->json([
            'translations' => $data,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function clearCache(): JsonResponse
    {
        $this->translationService->clearCache();

        return response()->json([
            'message' => 'Cache cleared successfully',
        ]);
    }
}
