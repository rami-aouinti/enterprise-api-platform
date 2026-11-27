<?php

declare(strict_types=1);

namespace App\Tests\Contract;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class ApiGovernanceRulesetTest extends TestCase
{
    public function testSpectralRulesetExtendsOpenApiRulesAndEnforcesPlatformGovernance(): void
    {
        $ruleset = Yaml::parseFile(dirname(__DIR__, 2) . '/.spectral.yaml');

        self::assertContains('spectral:oas', $ruleset['extends']);

        foreach ([
            'api-operation-security',
            'api-unauthorized-response',
            'api-forbidden-response',
            'api-operation-id-camel-case',
            'api-path-kebab-case',
            'api-info-version-semver',
        ] as $rule) {
            self::assertSame('error', $ruleset['rules'][$rule]['severity']);
        }
    }

    public function testExistingOpenApiContractsAreValidYamlAndUseSemanticVersions(): void
    {
        foreach (['customer-service.openapi.yaml', 'api-registry.openapi.yaml'] as $contractFile) {
            $contract = Yaml::parseFile(dirname(__DIR__, 2) . '/docs/api/' . $contractFile);

            self::assertSame('3.1.0', $contract['openapi']);
            self::assertMatchesRegularExpression('/^\\d+\\.\\d+\\.\\d+$/', $contract['info']['version']);
        }
    }

    public function testOasdiffFailsOnBreakingChangesAndUsesTheExceptionRegister(): void
    {
        $config = Yaml::parseFile(dirname(__DIR__, 2) . '/.oasdiff.yaml');

        self::assertSame('ERR', $config['fail-on']);
        self::assertSame('./docs/api/compatibility-exceptions.txt', $config['err-ignore']);
        self::assertFileExists(dirname(__DIR__, 2) . '/docs/api/compatibility-exceptions.txt');
    }
}
