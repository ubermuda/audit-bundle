<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle;

/**
 * The channels the admin filter offers. A channel is a plain string on a
 * record, so the consuming application names its own set and answers it here.
 */
interface AuditChannelCatalogInterface
{
    /** @return list<string> */
    public function channels(): array;
}
