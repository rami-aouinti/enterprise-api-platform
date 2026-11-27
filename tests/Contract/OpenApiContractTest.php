<?php

declare(strict_types=1);

namespace App\Tests\Contract;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class OpenApiContractTest extends TestCase
{
    public function testCustomerOpenApiContractDeclaresAllResourceOperations(): void
    {
        $contract = Yaml::parseFile(dirname(__DIR__, 2) . '/docs/api/customer-service.openapi.yaml');

        self::assertSame('3.1.0', $contract['openapi']);
        self::assertSame('1.0.0', $contract['info']['version']);
        self::assertArrayHasKey('get', $contract['paths']['/customers']);
        self::assertArrayHasKey('post', $contract['paths']['/customers']);
        self::assertArrayHasKey('get', $contract['paths']['/customers/{id}']);
        self::assertArrayHasKey('patch', $contract['paths']['/customers/{id}']);
        self::assertArrayHasKey('Customer', $contract['components']['schemas']);
        self::assertArrayHasKey('Problem', $contract['components']['schemas']);
    }
}
