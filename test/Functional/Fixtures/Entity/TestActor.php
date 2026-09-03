<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle\Test\Functional\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Ubermuda\AuditBundle\AuditActorInterface;

/**
 * The actor an application supplies through resolve_target_entities. Doctrine
 * cannot build metadata for an interface, so audit_log.actor_id needs a real
 * table to point at before the schema can be created.
 */
#[ORM\Entity]
#[ORM\Table(name: 'test_actor')]
class TestActor implements AuditActorInterface, UserInterface
{
    #[ORM\Column]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    public ?int $id = null;

    /**
     * @param non-empty-string $email
     * @param list<string>     $roles
     */
    public function __construct(
        /** @var non-empty-string */
        #[ORM\Column(length: 180)]
        public string $email = 'admin@example.test',

        #[ORM\Column(type: 'json')]
        public array $roles = ['ROLE_ADMIN'],
    ) {
    }

    #[\Override]
    public function auditLabel(): ?string
    {
        return $this->email;
    }

    #[\Override]
    public function auditIdentifier(): ?string
    {
        return null === $this->id ? null : (string) $this->id;
    }

    #[\Override]
    public function getRoles(): array
    {
        return $this->roles;
    }


    #[\Override]
    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
