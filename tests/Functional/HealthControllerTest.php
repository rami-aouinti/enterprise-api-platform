<?php

declare(strict_types=1);

namespace App\\Tests\\Functional;

use Symfony\\Bundle\\FrameworkBundle\\Test\\WebTestCase;

final class HealthControllerTest extends WebTestCase
{
    public function testLivenessEndpointReturnsSuccessfulJsonResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertJsonStringEqualsJsonString(
            '{"status":"ok","service":"customer-api"}',
            (string) $client->getResponse()->getContent(),
        );
    }

    public function testUnknownRouteReturnsNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/does-not-exist');

        self::assertResponseStatusCodeSame(404);
    }

    public function testHealthEndpointRejectsUnsupportedHttpMethod(): void
    {
        $client = static::createClient();
        $client->request('POST', '/health');

        self::assertResponseStatusCodeSame(405);
    }
}
