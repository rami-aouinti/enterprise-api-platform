<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final readonly class KeycloakUser implements UserInterface
{
    /**
     * @param non-empty-string $identifier
     * @param list<string> $roles
     * @param list<string> $scopes
     */
    public function __construct(
        private string $identifier,
        private array $roles,
        private array $scopes = [],
    ) {}

    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return array_values(array_unique(['ROLE_USER', ...$this->roles]));
    }

    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes, true);
    }

    /**
     * @return list<string>
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function eraseCredentials(): void {}
}
