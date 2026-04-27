<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class TranslationExportController extends Controller
{
    public function __construct(
        private readonly TranslationService $translationService
    ) {
    }

    #[OA\Get(
        path: '/translations/export',
        summary: 'Export translations for a specific locale',
        tags: ['Translations'],
        parameters: [
            new OA\Parameter(
                name: 'locale',
                in: 'query',
                description: 'Locale code (e.g., en, ar, fr)',
                required: false,
                schema: new OA\Schema(type: 'string', default: 'en')
            ),
            new OA\Parameter(
                name: 'tags',
                in: 'query',
                description: 'Filter by tags (comma-separated or array)',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'tag1,tag2')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Translations exported successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'locale', type: 'string', example: 'en'),
                        new OA\Property(
                            property: 'translations',
                            type: 'object',
                            example: ['welcome' => 'Welcome', 'goodbye' => 'Goodbye']
                        ),
                        new OA\Property(property: 'generated_at', type: 'string', format: 'date-time'),
                    ]
                )
            ),
        ]
    )]
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

    #[OA\Get(
        path: '/translations/export/all',
        summary: 'Export all translations for all locales',
        tags: ['Translations'],
        parameters: [
            new OA\Parameter(
                name: 'tags',
                in: 'query',
                description: 'Filter by tags (comma-separated or array)',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'tag1,tag2')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'All translations exported successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'translations',
                            type: 'object',
                            example: [
                                'en' => ['welcome' => 'Welcome', 'goodbye' => 'Goodbye'],
                                'ar' => ['welcome' => 'مرحبا', 'goodbye' => 'وداعا'],
                            ]
                        ),
                        new OA\Property(property: 'generated_at', type: 'string', format: 'date-time'),
                    ]
                )
            ),
        ]
    )]
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

    #[OA\Post(
        path: '/translations/cache/clear',
        summary: 'Clear translation cache',
        tags: ['Translations'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cache cleared successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Cache cleared successfully'),
                    ]
                )
            ),
        ]
    )]
    public function clearCache(): JsonResponse
    {
        $this->translationService->clearCache();

        return response()->json([
            'message' => 'Cache cleared successfully',
        ]);
    }
}
