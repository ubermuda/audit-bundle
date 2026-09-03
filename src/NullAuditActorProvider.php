<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle;

final readonly class NullAuditActorProvider implements AuditActorProviderInterface
{
    public const string CHANNEL = 'system';

    #[\Override]
    public function currentActor(): AuditActorContext
    {
        return new AuditActorContext(null, null, self::CHANNEL);
    }
}
