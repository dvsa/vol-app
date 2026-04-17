<?php

declare(strict_types=1);

/**
 * RemoveTest.php
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\CommandHandler\Tm\Remove;
use Dvsa\Olcs\Api\Domain\Repository\TransportManager as TransportManagerRepo;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Transfer\Command\Tm\Remove as Cmd;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Class UpdateTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\TransportManager
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class RemoveTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Remove();
        $this->mockRepo('TransportManager', TransportManagerRepo::class);

        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            TransportManagerEntity::TRANSPORT_MANAGER_STATUS_REMOVED,
        ];
        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 1
        ];

        $command = Cmd::create($data);

        $mockTransportManager = m::mock(TransportManagerEntity::class);
        $mockTransportManager->expects('setRemovedDate')->with(m::type(\DateTime::class));
        $mockTransportManager->expects('setTmStatus')
            ->with($this->refData[TransportManagerEntity::TRANSPORT_MANAGER_STATUS_REMOVED]);
        $mockTransportManager = $this->expectedCacheClearFromUserCollection($mockTransportManager);

        $this->repoMap['TransportManager']
            ->shouldReceive('fetchById')
            ->with(1)
            ->andReturn(
                $mockTransportManager
            )->shouldReceive('save');

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => ['Removed transport manager.']
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
