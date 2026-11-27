<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class KeycloakUserProvider implements AttributesBasedUserProviderInterface
{
    public function loadUserByIdentifier(string $identifier, array $attributes = []): UserInterface
    {
        $realmRoles = $attributes['realm_access']['roles'] ?? [];
        if (!is_array($realmRoles)) {
            $realmRoles = [];
        }

        $roles = [];
        foreach ($realmRoles as $realmRole) {
            if (!is_string($realmRole)) {
                continue;
            }

            $mappedRole = match ($realmRole) {
                'api.read' => 'ROLE_API_READ',
                'api.write' => 'ROLE_API_WRITE',
                'platform-admin' => 'ROLE_PLATFORM_ADMIN',
                default => null,
            };

            if ($mappedRole !== null) {
                $roles[] = $mappedRole;
            }
        }

        if (in_array('ROLE_PLATFORM_ADMIN', $roles, true)) {
            $roles[] = 'ROLE_API_READ';
            $roles[] = 'ROLE_API_WRITE';
        }

        return new KeycloakUser($identifier, array_values(array_unique($roles)));
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof KeycloakUser) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s".', $user::class));
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return is_a($class, KeycloakUser::class, true);
    }
}
