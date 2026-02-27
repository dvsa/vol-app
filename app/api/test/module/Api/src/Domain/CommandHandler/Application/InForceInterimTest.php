<?php

declare(strict_types=1);

/**
 * Grant Interim Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\InForceInterim;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;
use Dvsa\Olcs\Transfer\Command\Application\PrintInterimDocument;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Application\InForceInterim as Cmd;

/**
 * Grant Interim Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class InForceInterimTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new InForceInterim();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('GoodsDisc', \Dvsa\Olcs\Api\Domain\Repository\GoodsDisc::class);
        $this->mockRepo('CommunityLic', \Dvsa\Olcs\Api\Domain\Repository\CommunityLic::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            CommunityLic::STATUS_PENDING,
            CommunityLic::STATUS_ACTIVE,
            ApplicationEntity::INTERIM_STATUS_INFORCE
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['id' => 111]);

        /** @var GoodsDisc $gd */
        $gd = m::mock(GoodsDisc::class)->makePartial();
        $gd->setCeasedDate(null);

        $gds = [
            $gd
        ];

        /** @var LicenceVehicle $lv */
        $lv = m::mock(LicenceVehicle::class)->makePartial();
        $lv->setGoodsDiscs($gds);

        $lvs = [$lv];

        /** @var CommunityLic $cl */
        $cl = m::mock(CommunityLic::class)->makePartial();
        $cl->setStatus($this->refData[CommunityLic::STATUS_PENDING]);

        $cls = [$cl];

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);
        $licence->setCommunityLics($cls);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setLicenceVehicles($lvs);
        $application->setLicence($licence);

        $lv->setInterimApplication($application);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->with($application);

        $this->repoMap['GoodsDisc']->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (GoodsDisc $gd) use ($lv) {
                    $this->assertEquals('Y', $gd->getIsInterim());
                    $this->assertSame($lv, $gd->getLicenceVehicle());
                }
            );

        $this->repoMap['CommunityLic']->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (CommunityLic $newCl) use ($cl) {
                    $this->assertSame($cl, $newCl);
                    $this->assertEquals(date('Y-m-d'), $newCl->getSpecifiedDate()->format('Y-m-d'));
                    $this->assertSame($this->refData[CommunityLic::STATUS_ACTIVE], $newCl->getStatus());
                    $cl->setId(123);
                }
            );

        $result1 = new Result();
        $result1->addMessage('GenerateBatch');
        $expectedData = [
            'isBatchReprint' => false,
            'communityLicenceIds' => [123],
            'licence' => 222,
            'identifier' => 111
        ];
        $this->expectedSideEffect(GenerateBatch::class, $expectedData, $result1);

        $result2 = new Result();
        $result2->addMessage('PrintInterimDocument');
        $this->expectedSideEffect(PrintInterimDocument::class, ['id' => 111], $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '1 Vehicle(s) specified',
                '1 Goods Disc(s) created',
                '1 Goods Disc(s) ceased',
                '1 Community licence(s) activated',
                'GenerateBatch',
                'PrintInterimDocument',
                'Interim status updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        // Assertions

        $this->assertNotNull($gd->getCeasedDate());
        $this->assertSame($this->refData[ApplicationEntity::INTERIM_STATUS_INFORCE], $application->getInterimStatus());
    }
}
