<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cache;

use Dvsa\Olcs\Api\Domain\CommandHandler\Cache\Clear as Handler;
use Dvsa\Olcs\Transfer\Command\Cache\Clear as Cmd;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class ClearTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Handler();
        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create([]);

        $this->mockedSmServices[CacheEncryption::class]->expects('clearAllItems')->andReturnTrue();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['All caches cleared'], $result->getMessages());
    }

    public function testHandleCommandWithCqrsCacheId(): void
    {
        $command = Cmd::create(['cacheIds' => [CacheEncryption::CQRS_IDENTIFIER]]);

        $this->mockedSmServices[CacheEncryption::class]->expects('clearCqrsItems')->andReturnTrue();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['CQRS caches cleared'], $result->getMessages());
    }

    public function testHandleCommandWithCustomCacheId(): void
    {
        $command = Cmd::create(['cacheIds' => [CacheEncryption::SYS_PARAM_LIST_IDENTIFIER]]);

        $this->mockedSmServices[CacheEncryption::class]
            ->expects('clearItemsByType')
            ->with(CacheEncryption::SYS_PARAM_LIST_IDENTIFIER)
            ->andReturnTrue();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [sprintf('Cache type cleared: %s', CacheEncryption::SYS_PARAM_LIST_IDENTIFIER)],
            $result->getMessages()
        );
    }

    public function testHandleCommandReportsRuntimeExceptionAsMessage(): void
    {
        $command = Cmd::create(['cacheIds' => [CacheEncryption::CQRS_IDENTIFIER]]);

        $this->mockedSmServices[CacheEncryption::class]
            ->expects('clearCqrsItems')
            ->andThrow(new \RuntimeException('Cache adapter does not support ClearByPrefixInterface'));

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            ['Failed to clear cqrs: Cache adapter does not support ClearByPrefixInterface'],
            $result->getMessages()
        );
    }

    public function testHandleCommandIgnoresUnknownCacheId(): void
    {
        $command = Cmd::create(['cacheIds' => ['unknown_type']]);

        $this->mockedSmServices[CacheEncryption::class]->shouldNotReceive('clearAllItems');
        $this->mockedSmServices[CacheEncryption::class]->shouldNotReceive('clearCqrsItems');
        $this->mockedSmServices[CacheEncryption::class]->shouldNotReceive('clearItemsByType');

        $result = $this->sut->handleCommand($command);

        $this->assertEquals([], $result->getMessages());
    }

    public function testHandleCommandWithDoctrineId(): void
    {
        $command = Cmd::create(['cacheIds' => [CacheEncryption::DOCTRINE_IDENTIFIER]]);

        $this->mockedSmServices[CacheEncryption::class]->expects('clearDoctrineItems')->andReturnTrue();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['Doctrine caches cleared'], $result->getMessages());
    }

    public function testHandleCommandWithMixedCacheIds(): void
    {
        $command = Cmd::create(['cacheIds' => [CacheEncryption::CQRS_IDENTIFIER, CacheEncryption::TRANSLATION_KEY_IDENTIFIER]]);

        $this->mockedSmServices[CacheEncryption::class]->expects('clearCqrsItems')->andReturnTrue();
        $this->mockedSmServices[CacheEncryption::class]
            ->expects('clearItemsByType')
            ->with(CacheEncryption::TRANSLATION_KEY_IDENTIFIER)
            ->andReturnTrue();

        $result = $this->sut->handleCommand($command);

        $this->assertContains('CQRS caches cleared', $result->getMessages());
        $this->assertContains(
            sprintf('Cache type cleared: %s', CacheEncryption::TRANSLATION_KEY_IDENTIFIER),
            $result->getMessages()
        );
    }
}
