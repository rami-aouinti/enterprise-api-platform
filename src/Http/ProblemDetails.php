<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ProblemDetails
{
    /** @param array<string, mixed> $extensions */
    public static function response(Request $request, int $status, string $slug, string $title, string $detail, array $extensions = []): JsonResponse
    {
        return new JsonResponse([
            'type' => sprintf('https://api.enterprise-platform.test/problems/%s', $slug),
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => $request->getPathInfo(),
            ...$extensions,
        ], $status, ['Content-Type' => 'application/problem+json']);
    }
}
