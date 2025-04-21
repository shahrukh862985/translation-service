<?php

namespace App\Http\Controllers;

use App\Services\ExportService;

class ExportTranslationsController extends Controller
{
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * @OA\Get(
     *     path="/api/export/translations/{locale?}",
     *     summary="Export translations",
     *     description="Exports translations for a specific locale or all locales",
     *     operationId="exportTranslations",
     *     tags={"Export Translations"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="locale",
     *         in="path",
     *         description="Language locale code (optional - if not provided, all translations will be exported)",
     *         required=false,
     *         @OA\Schema(type="string", example="en")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Exported translations data",
     *                 example={
     *                     "en": {
     *                         "welcome": "Welcome to our application",
     *                         "homepage.greeting": "Hello, user!",
     *                         "common.buttons.save": "Save"
     *                     },
     *                     "fr": {
     *                         "welcome": "Bienvenue à notre application",
     *                         "homepage.greeting": "Bonjour, utilisateur!",
     *                         "common.buttons.save": "Enregistrer"
     *                     }
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="processing_time_ms", type="number", format="float", example=153.67)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Locale not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The requested locale does not exist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function export($locale = null)
    {
        $startTime = microtime(true);

        if ($locale) {
            $data = $this->exportService->exportForLocale($locale);
        } else {
            $data = $this->exportService->exportAll();
        }

        $processingTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

        return response()->json([
            'data' => $data,
            'meta' => [
                'processing_time_ms' => round($processingTime, 2),
            ]
        ]);
    }
}
