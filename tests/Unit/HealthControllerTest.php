<?php

declare(strict_types=1);

namespace App\\Tests\\Unit;

use App\\Controller\\HealthController;
use Doctrine\\DBAL\\Connection;
use Doctrine\\DBAL\\Exception\\ConnectionException;
use PHPUnit\\Framework\\TestCase;

final class HealthControllerTest extends TestCase
{
    public function testReadinessReturnsReadyWhenDatabaseQuerySucceeds(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('executeQuery')
            ->with('SELECT 1')
            ->willReturn($this->createMock(\\Doctrine\\DBAL\\Result::class));

        $response = (new HealthController($connection))->ready();

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
            ->willThrowException($this->createMock(ConnectionException::class));

        $response = (new HealthController($connection))->ready();

        self::assertSame(503, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            '{"status":"not_ready","database":"unavailable"}',
            (string) $response->getContent(),
        );
    }
}
