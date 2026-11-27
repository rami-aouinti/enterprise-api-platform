<?php

declare(strict_types=1);

namespace App\Controller;

use App\Customer\Application\CustomerInputValidator;
use App\Customer\Application\CustomerRepository;
use App\Http\ProblemDetails;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use JsonException;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/customers', name: 'api_v1_customers_')]
final readonly class CustomerController
{
    public function __construct(
        private CustomerRepository $customers,
        private CustomerInputValidator $validator,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page = filter_var($request->query->get('page', '1'), FILTER_VALIDATE_INT);
        $perPage = filter_var($request->query->get('per_page', '20'), FILTER_VALIDATE_INT);

        if ($page === false || $page < 1 || $perPage === false || $perPage < 1 || $perPage > 100) {
            return ProblemDetails::response($request, 400, 'invalid-pagination', 'Invalid pagination parameters', 'Page must be a positive integer and per_page must be between 1 and 100.');
        }

        $total = $this->customers->count();

        return new JsonResponse([
            'data' => $this->customers->findAll($perPage, ($page - 1) * $perPage),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $validation = $this->validator->validate($payload);
        if ($validation['errors'] !== []) {
            return ProblemDetails::response($request, 422, 'validation-error', 'Validation failed', 'One or more fields are invalid.', ['errors' => $validation['errors']]);
        }

        try {
            $customer = $this->customers->create($validation['data']);
        } catch (UniqueConstraintViolationException) {
            return $this->emailConflict($request);
        }

        return new JsonResponse($customer, JsonResponse::HTTP_CREATED, ['Location' => sprintf('/api/v1/customers/%s', $customer['id'])]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'], methods: ['GET'])]
    public function show(Request $request, string $id): JsonResponse
    {
        $customer = $this->customers->find($id);

        return $customer === null ? $this->notFound($request) : new JsonResponse($customer);
    }

    #[Route('/{id}', name: 'update', requirements: ['id' => '[0-9a-fA-F-]{36}'], methods: ['PATCH'])]
    public function update(Request $request, string $id): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $validation = $this->validator->validate($payload, true);
        if ($validation['errors'] !== []) {
            return ProblemDetails::response($request, 422, 'validation-error', 'Validation failed', 'One or more fields are invalid.', ['errors' => $validation['errors']]);
        }

        try {
            $customer = $this->customers->update($id, $validation['data']);
        } catch (UniqueConstraintViolationException) {
            return $this->emailConflict($request);
        }

        return $customer === null ? $this->notFound($request) : new JsonResponse($customer);
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

    private function emailConflict(Request $request): JsonResponse
    {
        return ProblemDetails::response($request, 409, 'customer-email-conflict', 'Customer email already exists', 'A customer with this email address already exists.', ['errors' => ['email' => 'This email address is already in use.']]);
    }

    private function notFound(Request $request): JsonResponse
    {
        return ProblemDetails::response($request, 404, 'customer-not-found', 'Customer not found', 'The requested customer does not exist.');
    }
}
