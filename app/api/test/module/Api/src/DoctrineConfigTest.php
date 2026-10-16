<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api;

use Dvsa\Olcs\Api\Listener\OlcsEntityListener;
use Dvsa\Olcs\Api\Mvc\OlcsBlameableListener;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Gedmo\Translatable\TranslatableListener;
use PHPUnit\Framework\TestCase;

final class DoctrineConfigTest extends TestCase
{
    public function testDoctrineEventManagerSubscribers(): void
    {
        $config = require dirname(__DIR__, 4) . '/module/Api/config/module.config.php';

        $subscribers = $config['doctrine']['event_manager']['orm_default']['subscribers'];

        self::assertContains(OlcsEntityListener::class, $subscribers);
        self::assertContains(SoftDeleteableListener::class, $subscribers);
        self::assertContains(TranslatableListener::class, $subscribers);
        self::assertContains(OlcsBlameableListener::class, $subscribers);
    }

    public function testDoctrineEntityManagerUsesDefaultEventManager(): void
    {
        $config = require dirname(__DIR__, 4) . '/config/autoload/config.global.php';

        self::assertSame(
            'orm_default',
            $config['doctrine']['entity_manager']['orm_default']['event_manager'],
        );
    }
}
