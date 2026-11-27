<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\ApiRegistry\Application\ApiRegistryRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApiRegistryControllerTest extends WebTestCase
{
    private const string API_ID = '123e4567-e89b-42d3-a456-426614174000';
    public function testListApisReturnsFilteredPaginatedCollection(): void
    {
        $client = static::createClient();
        $repository = $this->repository();
        $repository->expects(self::once())->method('count')->with('customer', 'published', 'rest')->willReturn(1);
        $repository->expects(self::once())->method('findAll')->with(10, 0, 'customer', 'published', 'rest')->willReturn([$this->api()]);
        $client->request('GET', '/api/v1/registry/apis?q=customer&state=published&tag=rest&page=1&per_page=10');
        self::assertResponseIsSuccessful();
        $this->assertJsonSubset(['meta' => ['page' => 1, 'per_page' => 10, 'total' => 1, 'total_pages' => 1]], $client->getResponse()->getContent());
    }
    public function testCreateApiReturnsCreatedResourceAndLocation(): void
    {
        $client = static::createClient();
        $repository = $this->repository();
        $repository->expects(self::once())->method('create')->willReturn($this->api());
        $client->jsonRequest('POST', '/api/v1/registry/apis', ['name' => 'Customer API', 'owner' => 'Platform Team', 'tags' => ['rest']]);
        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('location', '/api/v1/registry/apis/' . self::API_ID);
        $this->assertJsonSubset(['id' => self::API_ID, 'lifecycle_state' => 'draft'], $client->getResponse()->getContent());
    }
    public function testInvalidApiMetadataReturnsValidationProblem(): void
    {
        $client = static::createClient();
        $this->repository();
        $client->jsonRequest('POST', '/api/v1/registry/apis', ['name' => '', 'owner' => 'Platform Team']);
        self::assertResponseStatusCodeSame(422);
        self::assertResponseHeaderSame('content-type', 'application/problem+json');
    }
    public function testRegisterOpenApiVersionReturnsCreatedDocument(): void
    {
        $client = static::createClient();
        $repository = $this->repository();
        $repository->expects(self::once())->method('find')->with(self::API_ID)->willReturn($this->api());
        $repository->expects(self::once())->method('addVersion')->with(self::API_ID, '1.0.0', ['openapi' => '3.1.0', 'info' => ['title' => 'Customer API', 'version' => '1.0.0'], 'paths' => []])->willReturn(['id' => '223e4567-e89b-42d3-a456-426614174000', 'api_id' => self::API_ID, 'version' => '1.0.0', 'document' => ['openapi' => '3.1.0'], 'created_at' => '2026-11-27T15:00:00+00:00']);
        $client->jsonRequest('POST', '/api/v1/registry/apis/' . self::API_ID . '/versions', ['version' => '1.0.0', 'document' => ['openapi' => '3.1.0', 'info' => ['title' => 'Customer API', 'version' => '1.0.0'], 'paths' => (object) []]]);
        self::assertResponseStatusCodeSame(201);
    }
    public function testInvalidOpenApiDocumentReturnsValidationProblem(): void
    {
        $client = static::createClient();
        $this->repository();
        $client->jsonRequest('POST', '/api/v1/registry/apis/' . self::API_ID . '/versions', ['version' => '1.0.0', 'document' => ['openapi' => '2.0', 'info' => ['title' => 'Old API', 'version' => '1.0.0'], 'paths' => []]]);
        self::assertResponseStatusCodeSame(422);
    }
    /** @param array<string, mixed> $expected */
    private function assertJsonSubset(array $expected, string|false $content): void
    {
        self::assertIsString($content);
        $actual = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($actual);
        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $actual);
            if (is_array($value)) {
                self::assertIsArray($actual[$key]);
                $this->assertJsonArraySubset($value, $actual[$key]);
            } else {
                self::assertSame($value, $actual[$key]);
            }
        }
    }
    /** @param array<string, mixed> $expected @param array<string, mixed> $actual */
    private function assertJsonArraySubset(array $expected, array $actual): void
    {
        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $actual);
            if (is_array($value)) {
                self::assertIsArray($actual[$key]);
                $this->assertJsonArraySubset($value, $actual[$key]);
            } else {
                self::assertSame($value, $actual[$key]);
            }
        }
    }
    /** @return ApiRegistryRepository&MockObject */
    private function repository(): ApiRegistryRepository&MockObject
    {
        $repository = $this->createMock(ApiRegistryRepository::class);
        static::getContainer()->set(ApiRegistryRepository::class, $repository);
        return $repository;
    }
    /** @return array<string, mixed> */
    private function api(): array
    {
        return ['id' => self::API_ID, 'name' => 'Customer API', 'description' => '', 'owner' => 'Platform Team', 'tags' => ['rest'], 'lifecycle_state' => 'draft', 'created_at' => '2026-11-27T15:00:00+00:00', 'updated_at' => '2026-11-27T15:00:00+00:00'];
    }
}
