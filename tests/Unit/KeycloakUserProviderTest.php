<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Security\KeycloakScopeVoter;
use App\Security\KeycloakUser;
use App\Security\KeycloakUserProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class KeycloakUserProviderTest extends TestCase
{
    public function testMapsKeycloakRealmRolesToSymfonyRoles(): void
    {
        $user = new KeycloakUserProvider()->loadUserByIdentifier('user-123', [
            'realm_access' => [
                'roles' => ['api.read', 'api.write', 'portal-user'],
            ],
        ]);

        self::assertInstanceOf(KeycloakUser::class, $user);
        self::assertSame('user-123', $user->getUserIdentifier());
        self::assertSame(['ROLE_USER', 'ROLE_API_READ', 'ROLE_API_WRITE'], $user->getRoles());
        self::assertSame([], $user->getScopes());
    }

    public function testPlatformAdminReceivesReadAndWritePermissions(): void
    {
        $user = new KeycloakUserProvider()->loadUserByIdentifier('admin-123', [
            'realm_access' => [
                'roles' => ['platform-admin'],
            ],
        ]);

        self::assertSame(
            ['ROLE_USER', 'ROLE_PLATFORM_ADMIN', 'ROLE_API_READ', 'ROLE_API_WRITE'],
            $user->getRoles(),
        );
    }

    public function testExtractsOnlySupportedOAuthScopesFromTokenClaim(): void
    {
        $user = new KeycloakUserProvider()->loadUserByIdentifier('client-123', [
            'scope' => 'openid customers:read customers:write unknown:admin',
        ]);

        self::assertSame(['customers:read', 'customers:write'], $user->getScopes());
        self::assertTrue($user->hasScope('customers:read'));
        self::assertFalse($user->hasScope('unknown:admin'));
    }

    public function testScopeVoterGrantsOnlyTheRequiredScope(): void
    {
        $user = new KeycloakUser('client-123', [], ['customers:read']);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $voter = new KeycloakScopeVoter();

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, null, ['SCOPE_CUSTOMERS_READ']),
        );
        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, null, ['SCOPE_CUSTOMERS_WRITE']),
        );
    }

    public function testUnknownAndMalformedRealmRolesDoNotGrantApiAccess(): void
    {
        $user = new KeycloakUserProvider()->loadUserByIdentifier('user-456', [
            'realm_access' => [
                'roles' => ['portal-user', null, 123],
            ],
            'scope' => 'unknown:admin',
        ]);

        self::assertSame(['ROLE_USER'], $user->getRoles());
        self::assertSame([], $user->getScopes());
    }
}
