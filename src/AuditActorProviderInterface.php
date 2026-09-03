<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle;

interface AuditActorProviderInterface
{
    public function currentActor(): AuditActorContext;
}
