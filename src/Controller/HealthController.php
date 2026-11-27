<?php

declare(strict_types=1);

namespace App\\Controller;

use Doctrine\\DBAL\\Connection;
use Symfony\\Component\\HttpFoundation\\JsonResponse;
use Symfony\\Component\\Routing\\Attribute\\Route;

final readonly class HealthController
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    #[Route('/health', name: 'health_live', methods: ['GET'])]
    public function live(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'service' => 'customer-api',
        ]);
    }

    #[Route('/health/ready', name: 'health_ready', methods: ['GET'])]
    public function ready(): JsonResponse
    {
        try {
            $this->connection->executeQuery('SELECT 1')->fetchOne();
        } catch (\\Throwable) {
            return new JsonResponse([
                'status' => 'not_ready',
                'database' => 'unavailable',
            ], JsonResponse::HTTP_SERVICE_UNAVAILABLE);
        }

        return new JsonResponse([
            'status' => 'ready',
            'database' => 'connected',
        ]);
    }
}
