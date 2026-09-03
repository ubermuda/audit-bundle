<?php

declare(strict_types=1);

namespace Ubermuda\AuditBundle;

/**
 * What an event happened to, as a type/id pair rather than an association: a
 * subject can be any of several unrelated classes, which no single resolvable
 * interface maps.
 */
final readonly class AuditSubject
{
    public function __construct(
        public string $type,
        public string $id,
    ) {
    }
}
