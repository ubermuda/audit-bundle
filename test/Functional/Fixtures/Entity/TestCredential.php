<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle\Test\Functional\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ubermuda\AuditBundle\AuditCredentialInterface;

/** The credential counterpart of TestActor; see that class for why it exists. */
#[ORM\Entity]
#[ORM\Table(name: 'test_credential')]
class TestCredential implements AuditCredentialInterface
{
    #[ORM\Column]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    public ?int $id = null;

    #[\Override]
    public function auditIdentifier(): ?string
    {
        return null === $this->id ? null : (string) $this->id;
    }
}
