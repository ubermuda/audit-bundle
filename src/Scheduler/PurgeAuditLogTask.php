<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle\Scheduler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Ubermuda\AuditBundle\AuditLogPurger;

/**
 * Enforcement of the retention window on a schedule. `audit:purge` is the
 * manual backstop.
 *
 * The expression is the `purge_schedule` configuration node, so an application
 * moves the tick off its own busy minute without replacing this class.
 */
#[AsCronTask('%ubermuda_audit.purge_schedule%')]
final readonly class PurgeAuditLogTask
{
    public function __construct(
        private AuditLogPurger $purger,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(): void
    {
        // The scheduler discards a task's own output, so one line per tick is
        // what makes purge liveness greppable in the worker logs.
        $this->logger->info('audit.purge_completed', ['purged' => $this->purger->purge()]);
    }
}
