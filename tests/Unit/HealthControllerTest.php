<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Controller\HealthController;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class HealthControllerTest extends TestCase
{
    public function testReadinessReturnsReadyWhenDatabaseQuerySucceeds(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('executeQuery')
            ->with('SELECT 1')
            ->willReturn($this->createMock(Result::class));

        $response = new HealthController($connection)->ready();

        self::assertSame(200, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            '{"status":"ready","database":"connected"}',
            (string) $response->getContent(),
        );
    }

    public function testReadinessReturnsServiceUnavailableWhenDatabaseIsDown(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('executeQuery')
            ->with('SELECT 1')
            ->willThrowException(new RuntimeException('Database unavailable'));

        $response = new HealthController($connection)->ready();

        self::assertSame(503, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            '{"status":"not_ready","database":"unavailable"}',
            (string) $response->getContent(),
        );
    }
}
