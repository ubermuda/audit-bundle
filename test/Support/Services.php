<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle\Test\Support;

use Psr\Container\ContainerInterface;

/** The test container answers `object`, so a typed fetch belongs in one place. */
final class Services
{
    /**
     * @template T of object
     *
     * @param class-string<T> $id
     *
     * @return T
     */
    public static function get(ContainerInterface $container, string $id): object
    {
        $service = $container->get($id);

        if (!$service instanceof $id) {
            throw new \LogicException(sprintf('The container answered %s with %s.', $id, get_debug_type($service)));
        }

        return $service;
    }
}
