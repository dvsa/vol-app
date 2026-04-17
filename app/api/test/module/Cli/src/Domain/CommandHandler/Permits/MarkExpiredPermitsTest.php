<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepo;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Cli\Domain\Command\Permits\MarkExpiredPermits as MarkExpiredPermitsCommand;
use Dvsa\Olcs\Cli\Domain\CommandHandler\Permits\MarkExpiredPermits;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * MarkExpiredPermits test
 */
class MarkExpiredPermitsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new MarkExpiredPermits();
        $this->mockRepo('IrhpPermit', IrhpPermitRepo::class);
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->refData[IrhpInterface::STATUS_EXPIRED] = m::mock(RefData::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $appId1 = 1;
        $appId2 = 2;
        $appId3 = 3;
        $appExpiryDate1 = '2019-12-26';
        $appExpiryDate2 = '2019-12-24';
        $appExpiryDate3 = '2019-12-25';
        $app2ExceptionMessage = 'exception message';

        $irhpApplication1 = m::mock(IrhpApplication::class);
        $irhpApplication1->shouldReceive('getId')->once()->withNoArgs()->andReturn($appId1);
        $irhpApplication1->shouldReceive('getMotExpiryDate')->once()->withNoArgs()->andReturn($appExpiryDate1);
        $irhpApplication1->shouldReceive('expireCertificate')
            ->once()
            ->with($this->refData[IrhpInterface::STATUS_EXPIRED])
            ->andReturnNull();
        $irhpApplication2 = m::mock(IrhpApplication::class);
        $irhpApplication2->shouldReceive('getId')->once()->withNoArgs()->andReturn($appId2);
        $irhpApplication2->shouldReceive('getMotExpiryDate')->once()->withNoArgs()->andReturn($appExpiryDate2);
        $irhpApplication2->shouldReceive('expireCertificate')
            ->once()
            ->with($this->refData[IrhpInterface::STATUS_EXPIRED])
            ->andThrow(\Exception::class, $app2ExceptionMessage);
        $irhpApplication3 = m::mock(IrhpApplication::class);
        $irhpApplication3->shouldReceive('getId')->once()->withNoArgs()->andReturn($appId3);
        $irhpApplication3->shouldReceive('getMotExpiryDate')->once()->withNoArgs()->andReturn($appExpiryDate3);
        $irhpApplication3->shouldReceive('expireCertificate')
            ->once()
            ->with($this->refData[IrhpInterface::STATUS_EXPIRED])
            ->andReturnNull();

        $this->repoMap['IrhpPermit']->shouldReceive('markAsExpired')->withNoArgs()->once();
        $this->repoMap['IrhpApplication']->shouldReceive('markAsExpired')->withNoArgs()->once();
        $this->repoMap['IrhpApplication']->shouldReceive('fetchAllValidRoadworthiness')
            ->once()
            ->withNoArgs()
            ->andReturn([$irhpApplication1, $irhpApplication2, $irhpApplication3]);
        $this->repoMap['IrhpApplication']->shouldReceive('save')->twice()->with(m::type(IrhpApplication::class));

        $result = $this->sut->handleCommand(MarkExpiredPermitsCommand::create([]));

        $this->assertEquals(
            [
                'Roadworthiness certificate ID ' . $appId1 . ' with MOT expiry ' . $appExpiryDate1 . ' has been expired',
                'Roadworthiness certificate ID ' . $appId2 . ' with MOT expiry ' . $appExpiryDate2 . ' was not expired: ' . $app2ExceptionMessage,
                'Roadworthiness certificate ID ' . $appId3 . ' with MOT expiry ' . $appExpiryDate3 . ' has been expired',
                '2 certificates have been expired out of 3 checked',
                'Expired permits have been marked',
            ],
            $result->getMessages()
        );
    }
}
