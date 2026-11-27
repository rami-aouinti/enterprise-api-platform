<?php
declare(strict_types=1);
namespace App\Tests\Unit;
use App\ApiRegistry\Application\ApiRegistryInputValidator;
use PHPUnit\Framework\TestCase;
final class ApiRegistryInputValidatorTest extends TestCase
{
    public function testApiMetadataIsNormalizedAndDefaultsAreApplied(): void
    {
        $result = new ApiRegistryInputValidator()->validateApi(['name' => ' Customer API ', 'owner' => ' Platform Team ', 'tags' => ['Customer-Data', ' customer-data ', 'REST']]);
        self::assertSame([], $result['errors']);
        self::assertSame('Customer API', $result['data']['name']);
        self::assertSame('Platform Team', $result['data']['owner']);
        self::assertSame(['customer-data', 'rest'], $result['data']['tags']);
        self::assertSame('', $result['data']['description']);
        self::assertSame('draft', $result['data']['lifecycle_state']);
    }
    public function testInvalidMetadataAndUnknownFieldsAreRejected(): void
    {
        $result = new ApiRegistryInputValidator()->validateApi(['name' => '', 'owner' => 'Platform', 'tags' => ['Invalid Tag'], 'is_admin' => true, 'lifecycle_state' => 'deleted']);
        self::assertArrayHasKey('name', $result['errors']);
        self::assertArrayHasKey('tags', $result['errors']);
        self::assertArrayHasKey('is_admin', $result['errors']);
        self::assertArrayHasKey('lifecycle_state', $result['errors']);
    }
    public function testValidOpenApiDocumentIsAccepted(): void
    {
        $result = new ApiRegistryInputValidator()->validateVersion(['version' => '1.2.0', 'document' => (object) ['openapi' => '3.1.0', 'info' => (object) ['title' => 'Customer API', 'version' => '1.2.0'], 'paths' => (object) []]]);
        self::assertSame([], $result['errors']);
        self::assertSame('1.2.0', $result['data']['version']);
        self::assertSame('3.1.0', $result['data']['document']['openapi']);
    }
    public function testOpenApiVersionMustMatchDocumentMetadata(): void
    {
        $result = new ApiRegistryInputValidator()->validateVersion(['version' => '1.2.0', 'document' => (object) ['openapi' => '3.1.0', 'info' => (object) ['title' => 'Customer API', 'version' => '2.0.0'], 'paths' => (object) []]]);
        self::assertArrayHasKey('document', $result['errors']);
    }
    public function testPatchMustContainAtLeastOneField(): void
    {
        $result = new ApiRegistryInputValidator()->validateApi([], true);
        self::assertArrayHasKey('_payload', $result['errors']);
    }
}
