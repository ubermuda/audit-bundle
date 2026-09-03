<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Ubermuda\AuditBundle\Controller\Admin\ListAuditLogController;

return static function (RoutingConfigurator $routes): void {
    $routes->add('ubermuda_audit_log_list', '%ubermuda_audit.route_prefix%')
        ->controller(ListAuditLogController::class)
        ->methods(['GET']);
};
