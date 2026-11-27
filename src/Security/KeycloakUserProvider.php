<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @implements AttributesBasedUserProviderInterface<KeycloakUser>
 */
final class KeycloakUserProvider implements AttributesBasedUserProviderInterface
{
    private const array SUPPORTED_SCOPES = [
        'customers:read',
        'customers:write',
        'apis:read',
        'apis:write',
    ];

    /**
     * @param non-empty-string $identifier
     * @param array<string, mixed> $attributes
     */
    public function loadUserByIdentifier(string $identifier, array $attributes = []): KeycloakUser
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

        // OAuth 2.0 represents the granted scopes as a space-delimited claim.
        // Only known application scopes are exposed to the authorization layer.
        $scopeClaim = $attributes['scope'] ?? '';
        $requestedScopes = is_string($scopeClaim)
            ? preg_split('/\s+/', trim($scopeClaim), -1, PREG_SPLIT_NO_EMPTY)
            : [];

        $scopes = array_values(array_intersect(
            self::SUPPORTED_SCOPES,
            is_array($requestedScopes) ? $requestedScopes : [],
        ));

        return new KeycloakUser(
            $identifier,
            array_values(array_unique($roles)),
            $scopes,
        );
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
