<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The window as static configuration, which is what an application that
 * supplies no policy of its own gets.
 */
final readonly class ParameterAuditRetentionPolicy implements AuditRetentionPolicyInterface
{
    public function __construct(
        #[Autowire(param: 'ubermuda_audit.retention_days')]
        private int $retentionDays,
    ) {
    }

    #[\Override]
    public function retentionDays(): int
    {
        return max(1, $this->retentionDays);
    }
}
