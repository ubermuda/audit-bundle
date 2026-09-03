<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle\Test\Functional;

use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\UX\Icons\UXIconsBundle;
use Symfony\UX\TwigComponent\TwigComponentBundle;
use Ubermuda\AdminBundle\UbermudaAdminBundle;
use Ubermuda\AuditBundle\AuditActorInterface;
use Ubermuda\AuditBundle\AuditChannelCatalogInterface;
use Ubermuda\AuditBundle\AuditCredentialInterface;
use Ubermuda\AuditBundle\Test\Functional\Fixtures\Entity\TestActor;
use Ubermuda\AuditBundle\Test\Functional\Fixtures\TestChannelCatalog;
use Ubermuda\AuditBundle\Test\Functional\Fixtures\Entity\TestCredential;
use Ubermuda\AuditBundle\UbermudaAuditBundle;

class AuditTestKernel extends Kernel
{
    use MicroKernelTrait;

    #[\Override]
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new TwigBundle(),
            new SecurityBundle(),
            new UXIconsBundle(),
            new TwigComponentBundle(),
            new UbermudaAdminBundle(),
            new UbermudaAuditBundle(),
            new DAMADoctrineTestBundle(),
        ];
    }

    #[\Override]
    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/ubermuda-audit/cache/'.$this->environment;
    }

    #[\Override]
    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/ubermuda-audit/log';
    }

    protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader): void
    {
        $container->extension('framework', [
            'secret' => 'test',
            'test' => true,
            'csrf_protection' => false,
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors' => ['log' => true],
            'session' => [
                'storage_factory_id' => 'session.storage.factory.mock_file',
            ],
            // The admin bundle's base template calls importmap('app'), so the
            // asset mapper needs an entrypoint of that name to resolve.
            'asset_mapper' => [
                'paths' => [__DIR__.'/Fixtures/assets' => 'assets'],
                'importmap_path' => __DIR__.'/Fixtures/importmap.php',
            ],
        ]);

        // Postgres, not sqlite: the entity pins JSONB and TIMESTAMP(6), and the
        // purger deletes through raw SQL that has to run on the real backend.
        $container->extension('doctrine', [
            'dbal' => [
                'url' => '%env(resolve:DATABASE_URL)%',
            ],
            'orm' => [
                'auto_mapping' => false,
                'mappings' => [
                    'TestFixtures' => [
                        'type' => 'attribute',
                        'dir' => __DIR__.'/Fixtures/Entity',
                        'prefix' => 'Ubermuda\\AuditBundle\\Test\\Functional\\Fixtures\\Entity',
                        'is_bundle' => false,
                    ],
                ],
                'resolve_target_entities' => [
                    AuditActorInterface::class => TestActor::class,
                    AuditCredentialInterface::class => TestCredential::class,
                ],
            ],
        ]);

        // The profiler backs the query-count assertion on the listing.
        $container->extension('framework', ['profiler' => ['only_exceptions' => false, 'collect' => false]]);

        $container->extension('twig', ['strict_variables' => true]);
        $container->extension('twig_component', [
            'anonymous_template_directory' => 'components/',
            'defaults' => [],
        ]);
        $container->extension('ux_icons', ['ignore_not_found' => true]);

        $this->configureServices($container);

        $container->extension('security', [
            'providers' => [
                'test_actors' => ['entity' => ['class' => TestActor::class, 'property' => 'email']],
            ],
            'firewalls' => [
                'main' => [
                    'lazy' => true,
                    'provider' => 'test_actors',
                    // An entry point, so an anonymous request to the admin route
                    // redirects the way it does in a real application.
                    'form_login' => ['login_path' => 'login', 'check_path' => 'login'],
                ],
            ],
        ]);
    }

    protected function configureServices(ContainerConfigurator $container): void
    {
        $services = $container->services();
        $services->set(TestChannelCatalog::class);
        $services->alias(AuditChannelCatalogInterface::class, TestChannelCatalog::class);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__.'/../../config/routes.php');

        // Stub route so path('app_dashboard') resolves in the admin base layout.
        $routes->add('app_dashboard', '/')->methods(['GET']);
        $routes->add('login', '/login')->methods(['GET', 'POST']);
    }
}
