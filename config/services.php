<?php

use Ubermuda\AuditBundle\AuditChannelCatalogInterface;
use Ubermuda\AuditBundle\AuditActorProviderInterface;
use Ubermuda\AuditBundle\AuditLoggerRegistryInterface;
use Ubermuda\AuditBundle\AuditRetentionPolicyInterface;
use Ubermuda\AuditBundle\EmptyAuditChannelCatalog;
use Ubermuda\AuditBundle\NullAuditActorProvider;
use Ubermuda\AuditBundle\NullAuditLoggerRegistry;
use Ubermuda\AuditBundle\ParameterAuditRetentionPolicy;

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('Ubermuda\\AuditBundle\\', __DIR__.'/../src/')
        ->exclude([
            __DIR__.'/../src/Entity/',
            __DIR__.'/../src/AuditActorContext.php',
            __DIR__.'/../src/AuditEvent.php',
            __DIR__.'/../src/AuditOutcome.php',
            __DIR__.'/../src/AuditSubject.php',
            __DIR__.'/../src/Command/Admin/AuditLogRow.php',
            __DIR__.'/../src/Command/Admin/ListAuditLogCommand.php',
            __DIR__.'/../src/Command/Admin/ListAuditLogView.php',
            __DIR__.'/../src/UbermudaAuditBundle.php',
        ]);

    // The four ports an application overrides by aliasing the interface at its
    // own service definition. Each default is inert rather than clever: no
    // actor, no channel list, no logger mapping, and a static window.
    $services->alias(AuditActorProviderInterface::class, NullAuditActorProvider::class);
    $services->alias(AuditChannelCatalogInterface::class, EmptyAuditChannelCatalog::class);
    $services->alias(AuditLoggerRegistryInterface::class, NullAuditLoggerRegistry::class);
    $services->alias(AuditRetentionPolicyInterface::class, ParameterAuditRetentionPolicy::class);
};
