<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class UbermudaAuditBundle extends AbstractBundle
{
    #[\Override]
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('route_prefix')
                    ->defaultValue('/admin/audit-log')
                    ->info('URL prefix the admin route is mounted under.')
                ->end()
                ->integerNode('retention_days')
                    ->defaultValue(180)
                    ->min(1)
                    ->info('How long a record is kept. This is the static answer; supply an AuditRetentionPolicyInterface to make the window dynamic.')
                ->end()
                ->scalarNode('purge_schedule')
                    ->defaultValue('45 * * * *')
                    ->info('Cron expression for the retention sweep. Move it off a minute the application already uses for other work.')
                ->end()
            ->end();
    }

    /**
     * @param array{route_prefix: string, retention_days: int, purge_schedule: string} $config
     */
    #[\Override]
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->setParameter('ubermuda_audit.route_prefix', $config['route_prefix']);
        $builder->setParameter('ubermuda_audit.retention_days', $config['retention_days']);
        $builder->setParameter('ubermuda_audit.purge_schedule', $config['purge_schedule']);

        $container->import('../config/services.php');
    }

    #[\Override]
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (!$builder->hasExtension('doctrine')) {
            return;
        }

        $builder->prependExtensionConfig('doctrine', [
            'orm' => [
                'mappings' => [
                    'UbermudaAuditBundle' => [
                        'type' => 'attribute',
                        'dir' => __DIR__.'/Entity',
                        'prefix' => 'Ubermuda\\AuditBundle\\Entity',
                        'alias' => 'UbermudaAudit',
                        'is_bundle' => false,
                    ],
                ],
            ],
        ]);
    }
}
