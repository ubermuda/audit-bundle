<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle\Test\Functional\Fixtures;

use Ubermuda\AuditBundle\AuditChannelCatalogInterface;

/** The channel list an application supplies, so the admin filter has an allowlist. */
final readonly class TestChannelCatalog implements AuditChannelCatalogInterface
{
    #[\Override]
    public function channels(): array
    {
        return ['session', 'mcp'];
    }
}
