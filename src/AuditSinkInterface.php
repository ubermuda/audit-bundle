<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('ubermuda_audit.sink')]
interface AuditSinkInterface
{
    public function write(AuditEvent $event): void;

    /**
     * A no-op for a sink that writes immediately.
     */
    public function flush(): void;
}
