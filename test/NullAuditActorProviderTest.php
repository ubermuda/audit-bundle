<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle\Test;

use Ubermuda\AuditBundle\NullAuditActorProvider;
use PHPUnit\Framework\TestCase;

final class NullAuditActorProviderTest extends TestCase
{
    public function test_yields_a_context_with_neither_actor_nor_credential(): void
    {
        $context = new NullAuditActorProvider()->currentActor();

        self::assertNull($context->actor);
        self::assertNull($context->credential);
        self::assertSame('system', $context->channel);
    }
}
