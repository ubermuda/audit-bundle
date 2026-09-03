<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Ubermuda\AuditBundle\AuditActorInterface;
use Ubermuda\AuditBundle\AuditCredentialInterface;
use Ubermuda\AuditBundle\AuditOutcome;
use Ubermuda\AuditBundle\Repository\AuditLogRepository;

/**
 * One recorded audit event. Rows are appended by DoctrineAuditSink through the
 * DBAL connection rather than this mapping, which exists so the schema has a
 * single declared source and a reader can hydrate a row.
 *
 * The subject is a type/id pair with no association: a subject is whatever the
 * application audits, and ResolveTargetEntityListener maps one interface to
 * exactly one class.
 *
 * Every column carries an explicit name, so the schema is the same whatever
 * naming strategy the consuming application configures.
 */
#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ORM\Index(name: 'idx_audit_log_subject', columns: ['subject_type', 'subject_id'])]
#[ORM\Index(name: 'idx_audit_log_operation_occurred_at', columns: ['operation', 'occurred_at'])]
#[ORM\Index(name: 'idx_audit_log_occurred_at', columns: ['occurred_at'])]
#[ORM\Table(name: 'audit_log')]
class AuditLog
{
    public const int MAX_OPERATION_LENGTH = 100;
    public const int MAX_LABEL_LENGTH = 255;
    public const int MAX_SUBJECT_TYPE_LENGTH = 50;
    public const int MAX_SUBJECT_ID_LENGTH = 64;

    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) ?Uuid $id = null;

    /**
     * @param array<string, scalar|null> $context
     */
    public function __construct(
        #[ORM\Column(length: self::MAX_OPERATION_LENGTH)]
        public string $operation,

        #[ORM\Column(length: 20, enumType: AuditOutcome::class)]
        public AuditOutcome $outcome,

        #[ORM\Column(length: 20)]
        public string $category,

        #[ORM\Column(length: 20)]
        public string $channel,

        /** Microsecond precision, because a burst of events inside one request must stay ordered. */
        #[ORM\Column(name: 'occurred_at', columnDefinition: 'TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL')]
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable(),

        #[ORM\Column(type: Types::JSONB, options: ['default' => '{}'])]
        public array $context = [],

        /**
         * SET NULL rather than CASCADE, so the row survives the actor row it
         * points at. An application that must erase a departing actor's own
         * records deletes them itself; this association only forgets the link.
         */
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        #[ORM\ManyToOne(targetEntity: AuditActorInterface::class)]
        public ?AuditActorInterface $actor = null,

        #[ORM\Column(name: 'actor_label', length: self::MAX_LABEL_LENGTH, nullable: true)]
        public ?string $actorLabel = null,

        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        #[ORM\ManyToOne(targetEntity: AuditCredentialInterface::class)]
        public ?AuditCredentialInterface $credential = null,

        #[ORM\Column(name: 'subject_type', length: self::MAX_SUBJECT_TYPE_LENGTH, nullable: true)]
        public ?string $subjectType = null,

        #[ORM\Column(name: 'subject_id', length: self::MAX_SUBJECT_ID_LENGTH, nullable: true)]
        public ?string $subjectId = null,
    ) {
    }
}
