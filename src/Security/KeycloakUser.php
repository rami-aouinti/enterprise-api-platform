<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final readonly class KeycloakUser implements UserInterface
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        private string $identifier,
        private array $roles,
    ) {
    }

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

    public function eraseCredentials(): void
    {
    }
}
