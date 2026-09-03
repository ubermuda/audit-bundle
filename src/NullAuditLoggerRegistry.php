<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle;

use Psr\Log\LoggerInterface;

/**
 * The default for an application that maps no category to a logger of its own.
 * MonologAuditSink then writes every category to its fallback logger.
 */
final readonly class NullAuditLoggerRegistry implements AuditLoggerRegistryInterface
{
    #[\Override]
    public function loggerFor(string $category): ?LoggerInterface
    {
        return null;
    }
}
