<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle\Test\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;
use Ubermuda\AuditBundle\AuditActorProviderInterface;
use Ubermuda\AuditBundle\AuditChannelCatalogInterface;
use Ubermuda\AuditBundle\AuditLoggerRegistryInterface;
use Ubermuda\AuditBundle\AuditRetentionPolicyInterface;
use Ubermuda\AuditBundle\Auditor;
use Ubermuda\AuditBundle\Entity\AuditLog;
use Ubermuda\AuditBundle\NullAuditActorProvider;
use Ubermuda\AuditBundle\NullAuditLoggerRegistry;
use Ubermuda\AuditBundle\ParameterAuditRetentionPolicy;
use Ubermuda\AuditBundle\Scheduler\PurgeAuditLogTask;
use Ubermuda\AuditBundle\Test\Functional\Fixtures\TestChannelCatalog;
use Ubermuda\AuditBundle\Test\Support\ScheduledTasks;
use Ubermuda\AuditBundle\Test\Support\Services;

/**
 * The bundle's seams are wiring, so nothing in a unit test would notice one
 * going missing.
 */
final class BundleWiringTest extends KernelTestCase
{
    public function test_each_port_answers_with_its_default_unless_the_application_overrides_it(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        self::assertInstanceOf(NullAuditActorProvider::class, $container->get(AuditActorProviderInterface::class));
        self::assertInstanceOf(NullAuditLoggerRegistry::class, $container->get(AuditLoggerRegistryInterface::class));
        self::assertInstanceOf(ParameterAuditRetentionPolicy::class, $container->get(AuditRetentionPolicyInterface::class));

        // The one this kernel overrides, which is what a consuming application does.
        self::assertInstanceOf(TestChannelCatalog::class, $container->get(AuditChannelCatalogInterface::class));
    }

    public function test_the_retention_default_is_one_hundred_and_eighty_days(): void
    {
        self::bootKernel();

        $policy = Services::get(self::getContainer(), AuditRetentionPolicyInterface::class);
        self::assertInstanceOf(AuditRetentionPolicyInterface::class, $policy);
        self::assertSame(180, $policy->retentionDays());
    }

    public function test_the_doctrine_sink_is_tagged_and_reaches_the_facade(): void
    {
        self::bootKernel();

        $auditor = Services::get(self::getContainer(), Auditor::class);
        self::assertInstanceOf(Auditor::class, $auditor);

        // The tag is the only thing joining a sink to the facade, so an empty
        // fan-out is a silently broken trail rather than an error.
        $sinks = new \ReflectionProperty(Auditor::class, 'sinks')->getValue($auditor);
        self::assertNotEmpty($sinks);
    }

    public function test_the_entity_maps_to_the_shipped_table_and_column_names(): void
    {
        self::bootKernel();

        $entityManager = Services::get(self::getContainer(), EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $metadata = $entityManager->getClassMetadata(AuditLog::class);

        self::assertSame('audit_log', $metadata->getTableName());

        // Explicit names, so the schema does not depend on the naming strategy
        // the consuming application happens to configure.
        foreach (['occurredAt' => 'occurred_at', 'actorLabel' => 'actor_label', 'subjectType' => 'subject_type', 'subjectId' => 'subject_id'] as $field => $column) {
            self::assertSame($column, $metadata->getColumnName($field));
        }
    }

    public function test_the_admin_route_is_mounted_under_the_configured_prefix(): void
    {
        self::bootKernel();

        $router = Services::get(self::getContainer(), RouterInterface::class);
        self::assertInstanceOf(RouterInterface::class, $router);
        $route = $router->getRouteCollection()->get('ubermuda_audit_log_list');

        self::assertNotNull($route);
        self::assertSame('/admin/audit-log', $route->getPath());
    }

    public function test_the_purge_runs_on_the_configured_cron_expression(): void
    {
        self::bootKernel();

        self::assertSame(
            '45 * * * *',
            ScheduledTasks::cronExpressions(self::getContainer())[PurgeAuditLogTask::class] ?? null,
        );
    }
}
