<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Security\KeycloakUser;
use App\Security\KeycloakUserProvider;
use PHPUnit\Framework\TestCase;

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

    public function testUnknownAndMalformedRealmRolesDoNotGrantApiAccess(): void
    {
        $user = new KeycloakUserProvider()->loadUserByIdentifier('user-456', [
            'realm_access' => [
                'roles' => ['portal-user', null, 123],
            ],
        ]);

        self::assertSame(['ROLE_USER'], $user->getRoles());
    }
}
