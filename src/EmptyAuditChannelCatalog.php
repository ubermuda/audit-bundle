<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle;

/**
 * The default for an application that names no channels. The admin screen then
 * renders no channel filter, and every value a record carries is still shown.
 */
final readonly class EmptyAuditChannelCatalog implements AuditChannelCatalogInterface
{
    #[\Override]
    public function channels(): array
    {
        return [];
    }
}
