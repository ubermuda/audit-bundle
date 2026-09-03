<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Authorization for the audit log admin. The controller gates on the
 * {@see self::ADMIN} attribute rather than a hardcoded role, so the access
 * policy lives in one place and is the extension point: decorate or replace
 * this voter (or add another that votes on the same attribute) to change it.
 *
 * @extends Voter<self::ADMIN, mixed>
 */
final class AuditLogVoter extends Voter
{
    public const string ADMIN = 'audit_log.admin';

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    #[\Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::ADMIN === $attribute;
    }

    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        // Default policy: any administrator reads the audit log.
        return $this->authorizationChecker->isGranted('ROLE_ADMIN');
    }
}
