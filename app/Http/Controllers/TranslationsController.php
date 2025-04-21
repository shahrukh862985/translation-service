<?php

namespace App\Http\Controllers;

use App\Http\Requests\TranslationCreateRequest;
use App\Http\Requests\TranslationUpdateRequest;
use App\Http\Resources\TranslationCollection;
use App\Http\Resources\TranslationResource;
use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use App\Repositories\LanguageRepository;
use App\Repositories\TagRepository;
use App\Repositories\TranslationRepository;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\ItemNotFoundException;

class TranslationsController extends Controller
{
    protected $translationRepository;
    protected $tagRepository;
    protected $langRepository;
    protected $exportService;


    public function __construct(
        TranslationRepository $translationRepository,
        TagRepository $tagRepository,
        LanguageRepository $langRepository,
        ExportService $exportService
    ) {
        $this->langRepository = $langRepository;
        $this->translationRepository = $translationRepository;
        $this->tagRepository = $tagRepository;
        $this->exportService = $exportService;
    }
    /**
     * @OA\Get(
     *     path="/api/translations",
     *     summary="Get list of translations",
     *     description="Returns a paginated list of translations with optional filtering",
     *     operationId="indexTranslations",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="key",
     *         in="query",
     *         description="Filter by translation key (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="welcome")
     *     ),
     *     @OA\Parameter(
     *         name="locale",
     *         in="query",
     *         description="Filter by locale code (exact match)",
     *         required=false,
     *         @OA\Schema(type="string", example="en")
     *     ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="Filter by translation content (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="welcome")
     *     ),
     *     @OA\Parameter(
     *         name="tag_id",
     *         in="query",
     *         description="Filter by tag ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="tag_name",
     *         in="query",
     *         description="Filter by tag name (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="homepage")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page (default: 15)",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="key", type="string", example="welcome_message"),
     *                     @OA\Property(property="content", type="string", example="Welcome to our application"),
     *                     @OA\Property(property="language", 
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="English"),
     *                         @OA\Property(property="code", type="string", example="en")
     *                     ),
     *                     @OA\Property(
     *                         property="tags",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="homepage")
     *                         )
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-20T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-20T12:00:00Z")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", example="http://localhost/api/translations?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://localhost/api/translations?page=5"),
     *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                 @OA\Property(property="next", type="string", example="http://localhost/api/translations?page=2")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="path", type="string", example="http://localhost/api/translations"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="to", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=75)
     *             )
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
    public function index(Request $request)
    {
        $filters = $request->only(['key', 'locale', 'content', 'tag_id', 'tag_name']);
        $perPage = $request->input('per_page', 15);

        $translations = $this->translationRepository->search($filters, $perPage);

        return new TranslationCollection($translations);
    }

    /**
     * @OA\Post(
     *     path="/api/translations",
     *     summary="Create a new translation",
     *     description="Creates a new translation entry with specified key, locale, content and optional tags",
     *     operationId="storeTranslation",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"key", "locale", "content"},
     *             @OA\Property(property="key", type="string", maxLength=255, example="welcome_message", description="Translation key identifier"),
     *             @OA\Property(property="locale", type="string", maxLength=10, example="en", description="Language locale code"),
     *             @OA\Property(property="content", type="string", example="Welcome to our application", description="Translated content"),
     *             @OA\Property(
     *                 property="tags", 
     *                 type="array", 
     *                 description="Optional tags to associate with the translation",
     *                 @OA\Items(type="string", example="homepage")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Translation created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data", 
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="key", type="string", example="welcome_message"),
     *                 @OA\Property(property="content", type="string", example="Welcome to our application"),
     *                 @OA\Property(property="language", 
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="English"),
     *                     @OA\Property(property="code", type="string", example="en")
     *                 ),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="homepage")
     *                     )
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-20T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-20T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="key",
     *                     type="array",
     *                     @OA\Items(type="string", example="The key field is required.")
     *                 ),
     *                 @OA\Property(
     *                     property="locale",
     *                     type="array",
     *                     @OA\Items(type="string", example="The locale field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Language not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The specified language was not found.")
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
    public function store(TranslationCreateRequest $request)
    {
        try {
            $tagIds = [];
            if ($request->has('tags')) {
                foreach ($request->tags as $tagName) {
                    $tag = $this->tagRepository->findOrFail(trim(strtolower($tagName)));
                    $tagIds[] = $tag->id;
                }
            }
            $language = $this->langRepository->findOrFail($request->input('locale'));
            $translation = $this->translationRepository->create(
                [
                    'key' => trim(strtolower($request->input('key'))),
                    'content' => $request->input('content'),
                    'language_id' => $language->id,
                ],
                $tagIds
            );
            // Invalidate cache
            $this->exportService->invalidateCache($request->input('locale'));

            return new TranslationResource($translation);
        } catch (ItemNotFoundException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 404);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/translations/{id}",
     *     summary="Get a specific translation",
     *     description="Returns detailed information for a specific translation by ID",
     *     operationId="showTranslation",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Translation ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data", 
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="key", type="string", example="welcome_message"),
     *                 @OA\Property(property="content", type="string", example="Welcome to our application"),
     *                 @OA\Property(property="language", 
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="English"),
     *                     @OA\Property(property="code", type="string", example="en")
     *                 ),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="homepage")
     *                     )
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-20T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-20T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Translation not found")
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
    public function show($id)
    {
        $translation = $this->translationRepository->find($id);

        if (!$translation) {
            return response()->json(['status' => false, 'message' => 'Translation not found'], 404);
        }

        return new TranslationResource($translation);
    }

    /**
     * @OA\Put(
     *     path="/api/translations/{id}",
     *     summary="Update an existing translation",
     *     description="Updates a translation with the specified data",
     *     operationId="updateTranslation",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Translation ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="key", type="string", maxLength=191, example="updated_welcome_message", description="Translation key identifier"),
     *             @OA\Property(property="locale", type="string", maxLength=10, example="fr", description="Language locale code"),
     *             @OA\Property(property="content", type="string", example="Bienvenue à notre application", description="Translated content"),
     *             @OA\Property(
     *                 property="tags", 
     *                 type="array", 
     *                 description="Optional tags to associate with the translation",
     *                 @OA\Items(type="string", example="homepage")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translation updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data", 
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="key", type="string", example="welcome_message"),
     *                 @OA\Property(property="locale", type="string", example="en"),
     *                 @OA\Property(property="content", type="string", example="Welcome to the Application"),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(type="string", example="desktop")
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation, language or tag not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Translation not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="key",
     *                     type="array",
     *                     @OA\Items(type="string", example="The key field is required when present.")
     *                 )
     *             )
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
    public function update(TranslationUpdateRequest $request, $id)
    {
        try {
            $tagIds = [];
            if ($request->has('tags')) {
                foreach ($request->tags as $tagName) {
                    $tag = $this->tagRepository->findOrFail($tagName);
                    $tagIds[] = $tag->id;
                }
            }
            $oldTranslationObj = $this->translationRepository->find($id);
            $language = $this->langRepository->findOrFail($request->input('locale'));
            $translation = $this->translationRepository->update(
                $id,
                [
                    'key' => trim(strtolower($request->input('key'))),
                    'content' => $request->input('content'),
                    'language_id' => $language->id,
                ],
                $tagIds
            );

            // Invalidate cache for both old and new locale if changed
            $this->exportService->invalidateCache($oldTranslationObj->language->code);
            if ($oldTranslationObj->language->code !== $translation->language->code) {
                $this->exportService->invalidateCache($translation->language->code);
            }

            return new TranslationResource($translation);
        } catch (ItemNotFoundException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Translation not found'], 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/translations/{id}",
     *     summary="Delete a translation",
     *     description="Deletes a translation and invalidates related cache",
     *     operationId="destroyTranslation",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Translation ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translation deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Translation deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Translation not found")
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
    public function destroy($id)
    {
        $translation = Translation::with('language')->find($id);

        if (!$translation) {
            return response()->json(['message' => 'Translation not found'], 404);
        }

        $locale = $translation->language->code;

        $translation->delete();

        // Invalidate cache
        $this->exportService->invalidateCache($locale);

        return response()->json(['message' => 'Translation deleted successfully']);
    }
}
