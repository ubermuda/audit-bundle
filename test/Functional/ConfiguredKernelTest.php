<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle\Test\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\RouterInterface;
use Ubermuda\AuditBundle\AuditRetentionPolicyInterface;
use Ubermuda\AuditBundle\Scheduler\PurgeAuditLogTask;
use Ubermuda\AuditBundle\Test\Support\ScheduledTasks;

/** Every configuration node has to reach something, or it is decoration. */
final class ConfiguredKernelTest extends KernelTestCase
{
    #[\Override]
    protected static function getKernelClass(): string
    {
        return ConfiguredAuditKernel::class;
    }

    public function test_the_configuration_moves_the_prefix_the_window_and_the_schedule(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $router = $container->get(RouterInterface::class);
        self::assertInstanceOf(RouterInterface::class, $router);
        self::assertSame('/ops/trail', $router->getRouteCollection()->get('ubermuda_audit_log_list')?->getPath());

        $policy = $container->get(AuditRetentionPolicyInterface::class);
        self::assertInstanceOf(AuditRetentionPolicyInterface::class, $policy);
        self::assertSame(30, $policy->retentionDays());

        self::assertSame(
            '15 3 * * *',
            ScheduledTasks::cronExpressions($container)[PurgeAuditLogTask::class] ?? null,
        );
    }
}

class ConfiguredAuditKernel extends AuditTestKernel
{
    #[\Override]
    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/ubermuda-audit-configured/cache/'.$this->environment;
    }

    #[\Override]
    protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader): void
    {
        parent::configureContainer($container, $loader);

        $container->extension('ubermuda_audit', [
            'route_prefix' => '/ops/trail',
            'retention_days' => 30,
            'purge_schedule' => '15 3 * * *',
        ]);
    }
}
