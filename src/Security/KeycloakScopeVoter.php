<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, mixed>
 */
final class KeycloakScopeVoter extends Voter
{
    private const array SCOPE_ATTRIBUTES = [
        'SCOPE_CUSTOMERS_READ' => 'customers:read',
        'SCOPE_CUSTOMERS_WRITE' => 'customers:write',
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return isset(self::SCOPE_ATTRIBUTES[$attribute]);
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null,
    ): bool {
        $user = $token->getUser();

        if (!$user instanceof KeycloakUser) {
            return false;
        }

        return $user->hasScope(self::SCOPE_ATTRIBUTES[$attribute]);
    }
}
