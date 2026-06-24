<?php

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/module/Api/src/Entity'])
    ->withPhpVersion(PhpVersion::PHP_83)
    ->withSets([
        DoctrineSetList::DOCTRINE_ORM_219,
    ]);