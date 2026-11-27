<?php

declare(strict_types=1);

namespace App\Controller;

use App\ApiRegistry\Application\ApiRegistryInputValidator;
use App\ApiRegistry\Application\ApiRegistryRepository;
use App\Http\ProblemDetails;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use JsonException;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/registry/apis', name: 'api_v1_registry_apis_')]
final readonly class ApiRegistryController
{
    public function __construct(private ApiRegistryRepository $apis, private ApiRegistryInputValidator $validator) {}
    #[Route('', name: 'list', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page = filter_var($request->query->get('page', '1'), FILTER_VALIDATE_INT);
        $perPage = filter_var($request->query->get('per_page', '20'), FILTER_VALIDATE_INT);
        $state = $request->query->get('state');
        $tag = $request->query->get('tag');
        $search = $request->query->get('q');
        if ($page === false || $page < 1 || $perPage === false || $perPage < 1 || $perPage > 100) {
            return ProblemDetails::response($request, 400, 'invalid-pagination', 'Invalid pagination parameters', 'Page must be a positive integer and per_page must be between 1 and 100.');
        }
        if ($state !== null && (!in_array($state, ['draft', 'published', 'deprecated'], true))) {
            return ProblemDetails::response($request, 400, 'invalid-api-state', 'Invalid API lifecycle state', 'State must be draft, published or deprecated.');
        }
        if ($search !== null && (($length = iconv_strlen($search, 'UTF-8')) === false || $length > 200)) {
            return ProblemDetails::response($request, 400, 'invalid-search', 'Invalid search query', 'Search query must be at most 200 characters.');
        }
        if ($tag !== null && (preg_match('/^[a-z0-9][a-z0-9-]{0,39}$/', $tag) !== 1)) {
            return ProblemDetails::response($request, 400, 'invalid-tag', 'Invalid tag filter', 'Tag filter must use lowercase letters, numbers or hyphens.');
        }
        $total = $this->apis->count($search, $state, $tag);
        return new JsonResponse(['data' => $this->apis->findAll($perPage, ($page - 1) * $perPage, $search, $state, $tag), 'meta' => ['page' => $page, 'per_page' => $perPage, 'total' => $total, 'total_pages' => (int) ceil($total / $perPage)]]);
    }
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }
        $validation = $this->validator->validateApi($payload);
        if ($validation['errors'] !== []) {
            return ProblemDetails::response($request, 422, 'validation-error', 'Validation failed', 'One or more fields are invalid.', ['errors' => $validation['errors']]);
        }
        try {
            $api = $this->apis->create($validation['data']);
        } catch (UniqueConstraintViolationException) {
            return ProblemDetails::response($request, 409, 'api-registry-conflict', 'API already registered', 'An API with the same name and owner is already registered.');
        }
        return new JsonResponse($api, JsonResponse::HTTP_CREATED, ['Location' => sprintf('/api/v1/registry/apis/%s', $api['id'])]);
    }
    #[Route('/{id}', name: 'show', requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'], methods: ['GET'])]
    public function show(Request $request, string $id): JsonResponse
    {
        $api = $this->apis->find($id);
        return $api === null ? ProblemDetails::response($request, 404, 'api-not-found', 'API not found', 'The requested API is not registered.') : new JsonResponse($api);
    }
    #[Route('/{id}', name: 'update', requirements: ['id' => '[0-9a-fA-F-]{36}'], methods: ['PATCH'])]
    public function update(Request $request, string $id): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }
        $validation = $this->validator->validateApi($payload, true);
        if ($validation['errors'] !== []) {
            return ProblemDetails::response($request, 422, 'validation-error', 'Validation failed', 'One or more fields are invalid.', ['errors' => $validation['errors']]);
        }
        try {
            $api = $this->apis->update($id, $validation['data']);
        } catch (UniqueConstraintViolationException) {
            return ProblemDetails::response($request, 409, 'api-registry-conflict', 'API already registered', 'An API with the same name and owner is already registered.');
        }
        return $api === null ? ProblemDetails::response($request, 404, 'api-not-found', 'API not found', 'The requested API is not registered.') : new JsonResponse($api);
    }
    #[Route('/{id}/versions', name: 'create_version', requirements: ['id' => '[0-9a-fA-F-]{36}'], methods: ['POST'])]
    public function createVersion(Request $request, string $id): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }
        $validation = $this->validator->validateVersion($payload);
        if ($validation['errors'] !== []) {
            return ProblemDetails::response($request, 422, 'invalid-openapi-document', 'Invalid API version', 'The version or OpenAPI document is invalid.', ['errors' => $validation['errors']]);
        }
        if ($this->apis->find($id) === null) {
            return ProblemDetails::response($request, 404, 'api-not-found', 'API not found', 'The requested API is not registered.');
        }
        try {
            $version = $this->apis->addVersion($id, $validation['data']['version'], $validation['data']['document']);
        } catch (UniqueConstraintViolationException) {
            return ProblemDetails::response($request, 409, 'api-version-conflict', 'API version already registered', 'This version already exists for the API.');
        }
        return new JsonResponse($version, JsonResponse::HTTP_CREATED, ['Location' => sprintf('/api/v1/registry/apis/%s/versions/%s', $id, $version['version'])]);
    }
    #[Route('/{id}/versions/{version}', name: 'show_version', requirements: ['id' => '[0-9a-fA-F-]{36}', 'version' => '\d+\.\d+\.\d+(?:-[A-Za-z0-9.-]+)?(?:\+[A-Za-z0-9.-]+)?'], methods: ['GET'])]
    public function showVersion(Request $request, string $id, string $version): JsonResponse
    {
        $result = $this->apis->findVersion($id, $version);
        return $result === null ? ProblemDetails::response($request, 404, 'api-version-not-found', 'API version not found', 'The requested API version does not exist.') : new JsonResponse($result);
    }
    /** @return array<string, mixed>|JsonResponse */
    private function decodePayload(Request $request): array|JsonResponse
    {
        if ($request->getContentTypeFormat() !== 'json') {
            return ProblemDetails::response($request, 415, 'unsupported-media-type', 'Unsupported media type', 'Send the request body using the application/json media type.');
        }
        try {
            $payload = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return ProblemDetails::response($request, 400, 'malformed-json', 'Malformed JSON', 'The request body must contain valid JSON.');
        }
        if (!$payload instanceof stdClass) {
            return ProblemDetails::response($request, 400, 'invalid-json-payload', 'Invalid JSON payload', 'The request body must be a JSON object.');
        }
        return get_object_vars($payload);
    }
}
