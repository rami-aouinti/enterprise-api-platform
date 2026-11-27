<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Customer\Application\CustomerRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CustomerControllerTest extends WebTestCase
{
    private const string CUSTOMER_ID = '123e4567-e89b-42d3-a456-426614174000';

    public function testCreateCustomerReturnsCreatedResourceAndLocation(): void
    {
        $client = static::createClient();
        $repository = $this->repository();
        $repository->expects(self::once())->method('create')->willReturn($this->customer());

        $client->jsonRequest('POST', '/api/v1/customers', [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.org',
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('location', '/api/v1/customers/' . self::CUSTOMER_ID);
        $this->assertJsonSubset(['id' => self::CUSTOMER_ID, 'email' => 'ada@example.org'], $client->getResponse()->getContent());
    }

    public function testListCustomersReturnsPaginatedCollection(): void
    {
        $client = static::createClient();
        $repository = $this->repository();
        $repository->expects(self::once())->method('count')->willReturn(1);
        $repository->expects(self::once())->method('findAll')->with(10, 0)->willReturn([$this->customer()]);

        $client->request('GET', '/api/v1/customers?page=1&per_page=10');

        self::assertResponseIsSuccessful();
        $this->assertJsonSubset(
            ['meta' => ['page' => 1, 'per_page' => 10, 'total' => 1, 'total_pages' => 1]],
            $client->getResponse()->getContent(),
        );
    }

    public function testGetCustomerReturnsNotFoundProblemWhenMissing(): void
    {
        $client = static::createClient();
        $repository = $this->repository();
        $repository->expects(self::once())->method('find')->with(self::CUSTOMER_ID)->willReturn(null);

        $client->request('GET', '/api/v1/customers/' . self::CUSTOMER_ID);

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('content-type', 'application/problem+json');
        $this->assertJsonSubset(['status' => 404, 'title' => 'Customer not found'], $client->getResponse()->getContent());
    }

    public function testInvalidCreatePayloadReturnsValidationProblem(): void
    {
        $client = static::createClient();
        $this->repository();

        $client->jsonRequest('POST', '/api/v1/customers', [
            'first_name' => '',
            'last_name' => 'Lovelace',
            'email' => 'invalid',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertResponseHeaderSame('content-type', 'application/problem+json');
        $this->assertJsonSubset(['status' => 422, 'title' => 'Validation failed'], $client->getResponse()->getContent());
    }

    public function testPatchUpdatesOnlyProvidedFields(): void
    {
        $client = static::createClient();
        $customer = $this->customer();
        $customer['status'] = 'inactive';
        $repository = $this->repository();
        $repository->expects(self::once())->method('update')
            ->with(self::CUSTOMER_ID, ['status' => 'inactive'])
            ->willReturn($customer);

        $client->jsonRequest('PATCH', '/api/v1/customers/' . self::CUSTOMER_ID, ['status' => 'inactive']);

        self::assertResponseIsSuccessful();
        $this->assertJsonSubset(['status' => 'inactive'], $client->getResponse()->getContent());
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

                continue;
            }

            self::assertSame($value, $actual[$key]);
        }
    }

    /** @param array<string, mixed> $expected
     *  @param array<string, mixed> $actual
     */
    private function assertJsonArraySubset(array $expected, array $actual): void
    {
        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $actual);

            if (is_array($value)) {
                self::assertIsArray($actual[$key]);
                $this->assertJsonArraySubset($value, $actual[$key]);

                continue;
            }

            self::assertSame($value, $actual[$key]);
        }
    }

    /** @return CustomerRepository&MockObject */
    private function repository(): CustomerRepository&MockObject
    {
        $repository = $this->createMock(CustomerRepository::class);
        static::getContainer()->set(CustomerRepository::class, $repository);

        return $repository;
    }

    /** @return array<string, mixed> */
    private function customer(): array
    {
        return [
            'id' => self::CUSTOMER_ID,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.org',
            'phone' => null,
            'status' => 'active',
            'created_at' => '2026-11-27T15:00:00+00:00',
            'updated_at' => '2026-11-27T15:00:00+00:00',
        ];
    }
}
