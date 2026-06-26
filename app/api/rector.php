<?php

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/module/Api/src/Entity',
        __DIR__ . '/module/Cli/src/Service/EntityGenerator'
    ])
    ->withPhpVersion(PhpVersion::PHP_83)
    ->withSets([
        DoctrineSetList::DOCTRINE_ORM_219,
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        DoctrineSetList::GEDMO_ANNOTATIONS_TO_ATTRIBUTES,
        DoctrineSetList::DOCTRINE_ORM_25,
        DoctrineSetList::DOCTRINE_ORM_28,
        DoctrineSetList::DOCTRINE_ORM_213,
        DoctrineSetList::DOCTRINE_ORM_214,
        DoctrineSetList::DOCTRINE_ORM_300,
    ]);