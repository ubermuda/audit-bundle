<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle\Command\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Ubermuda\AuditBundle\AuditLogPurger;

/**
 * Deletes audit records past the retention window. PurgeAuditLogTask runs the
 * same purge on a schedule; this command is the manual backstop and the dev seam.
 */
#[AsCommand(
    name: 'audit:purge',
    description: 'Delete audit records past the retention window. Safe for cron.',
)]
final class PurgeAuditLogCommand extends Command
{
    public function __construct(
        private readonly AuditLogPurger $purger,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->success(sprintf('Purged %d audit record(s).', $this->purger->purge()));

        return Command::SUCCESS;
    }
}
