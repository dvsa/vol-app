<?php

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/module', __DIR__ . '/test'])
    ->withPhpVersion(PhpVersion::PHP_83)
    ->withSets([
        DoctrineSetList::DOCTRINE_ORM_25,
        DoctrineSetList::DOCTRINE_ORM_28,
        DoctrineSetList::DOCTRINE_ORM_213,
        DoctrineSetList::DOCTRINE_ORM_214,
        DoctrineSetList::DOCTRINE_ORM_300,
    ]);