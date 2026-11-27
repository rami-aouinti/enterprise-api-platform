<?php

declare(strict_types=1);

namespace App\Tests\Contract;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class ApiRegistryOpenApiContractTest extends TestCase
{
    public function testApiRegistryContractDeclaresCatalogAndVersionOperations(): void
    {
        $contract = Yaml::parseFile(dirname(__DIR__, 2) . '/docs/api/api-registry.openapi.yaml');
        self::assertSame('3.1.0', $contract['openapi']);
        self::assertArrayHasKey('get', $contract['paths']['/registry/apis']);
        self::assertArrayHasKey('post', $contract['paths']['/registry/apis']);
        self::assertArrayHasKey('get', $contract['paths']['/registry/apis/{id}']);
        self::assertArrayHasKey('patch', $contract['paths']['/registry/apis/{id}']);
        self::assertArrayHasKey('post', $contract['paths']['/registry/apis/{id}/versions']);
        self::assertArrayHasKey('get', $contract['paths']['/registry/apis/{id}/versions/{version}']);
        self::assertArrayHasKey('ApiRegistryEntry', $contract['components']['schemas']);
        self::assertArrayHasKey('OpenApiDocument', $contract['components']['schemas']);
    }
}
