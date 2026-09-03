<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle\Menu;

use Ubermuda\AdminBundle\Menu\AdminMenuItemInterface;

final class AuditLogMenuItem implements AdminMenuItemInterface
{
    #[\Override]
    public function getLabel(): string
    {
        // The admin bundle's sidebar template has no translator, so labels stay raw.
        return 'Audit log';
    }

    #[\Override]
    public function getIcon(): string
    {
        return 'history';
    }

    #[\Override]
    public function getRouteName(): string
    {
        return 'ubermuda_audit_log_list';
    }

    #[\Override]
    public function getActiveRoutePrefix(): string
    {
        return 'ubermuda_audit_log_';
    }

    #[\Override]
    public function getPriority(): int
    {
        return 20;
    }
}
