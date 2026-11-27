<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Customer\Application\CustomerInputValidator;
use PHPUnit\Framework\TestCase;

final class CustomerInputValidatorTest extends TestCase
{
    public function testValidCreatePayloadIsNormalized(): void
    {
        $result = new CustomerInputValidator()->validate([
            'first_name' => ' Ada ',
            'last_name' => ' Lovelace ',
            'email' => ' ADA.LOVELACE@EXAMPLE.ORG ',
        ]);

        self::assertSame([], $result['errors']);
        self::assertSame('Ada', $result['data']['first_name']);
        self::assertSame('Lovelace', $result['data']['last_name']);
        self::assertSame('ada.lovelace@example.org', $result['data']['email']);
        self::assertSame('active', $result['data']['status']);
        self::assertNull($result['data']['phone']);
    }

    public function testInvalidEmailAndUnknownFieldsAreRejected(): void
    {
        $result = new CustomerInputValidator()->validate([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'not-an-email',
            'is_admin' => true,
        ]);

        self::assertArrayHasKey('email', $result['errors']);
        self::assertArrayHasKey('is_admin', $result['errors']);
    }

    public function testPatchRequiresAtLeastOneField(): void
    {
        $result = new CustomerInputValidator()->validate([], true);

        self::assertArrayHasKey('_payload', $result['errors']);
    }

    public function testPatchValidatesOnlyProvidedFields(): void
    {
        $result = new CustomerInputValidator()->validate(['status' => 'inactive'], true);

        self::assertSame([], $result['errors']);
        self::assertSame(['status' => 'inactive'], $result['data']);
    }
}
