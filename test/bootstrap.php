<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Ubermuda\AuditBundle\Test\Functional\AuditTestKernel;

require __DIR__.'/../vendor/autoload.php';

// One schema per run, built from the mapping the test kernel declares. A wrong
// bundle mapping therefore fails here rather than as a confusing missing table.
$kernel = new AuditTestKernel('test', true);
$kernel->boot();

$doctrine = $kernel->getContainer()->get('doctrine');

if (!$doctrine instanceof ManagerRegistry) {
    throw new LogicException('The test kernel has no Doctrine registry.');
}

$entityManager = $doctrine->getManager();

if (!$entityManager instanceof EntityManagerInterface) {
    throw new LogicException('The Doctrine registry answered no entity manager.');
}

$schemaTool = new SchemaTool($entityManager);
$metadata = $entityManager->getMetadataFactory()->getAllMetadata();
$schemaTool->dropSchema($metadata);
$schemaTool->createSchema($metadata);

$kernel->shutdown();
