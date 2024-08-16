<?php

/**
 * Create Snapshot Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\CreateSnapshot;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Create Snapshot Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateSnapshotTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateSnapshot();
        $this->mockRepo('Application', Application::class);

        $this->mockedSmServices['ReviewSnapshot'] = m::mock();
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);
        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testHandleCommand(
        $isVariation,
        $isGoods,
        $isRealUpgrade,
        $isSpecialRestricted,
        $event,
        $fileName,
        $description,
        $subCategory,
        $isExternal
    ) {
        $isInternal = !$isExternal;
        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn($isInternal);
        $command = Cmd::create(['id' => 111, 'event' => $event]);
        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setIsVariation($isVariation);
        $application->setLicence($licence);
        $application->shouldReceive('isGoods')
            ->andReturn($isGoods)
            ->shouldReceive('isRealUpgrade')
            ->andReturn($isRealUpgrade)
            ->shouldReceive('isSpecialRestricted')
            ->andReturn($isSpecialRestricted);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->mockedSmServices['ReviewSnapshot']->shouldReceive('generate')
            ->with($application, $isInternal)
            ->andReturn('<markup>');

        $expectedData = [
            'content' => base64_encode('<markup>'),
            'filename' => $fileName,
            'application' => 111,
            'busReg' => null,
            'case' => null,
            'irfoOrganisation' => null,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => null,
            'licence' => 222,
            'operatingCentre' => null,
            'opposition' => null,
            'category' => 9,
            'subCategory' => $subCategory,
            'description' => $description,
            'isExternal' => $isExternal,
            'isScan' => false,
            'issuedDate' => null,
            'metadata' => null
        ];
        $result1 = new Result();
        $result1->addMessage('Document created');
        $this->expectedSideEffect(Upload::class, $expectedData, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Snapshot generated',
                'Document created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithoutEvent()
    {
        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);
        $this->expectException(ValidationException::class);

        $command = Cmd::create(['id' => 111, 'event' => 'FOO']);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setIsVariation(true);
        $application->setLicence($licence);
        $application->shouldReceive('isGoods')
            ->andReturn(true)
            ->shouldReceive('isRealUpgrade')
            ->andReturn(true)
            ->shouldReceive('isSpecialRestricted')
            ->andReturn(false);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->mockedSmServices['ReviewSnapshot']->shouldReceive('generate')
            ->with($application, false)
            ->andReturn('<markup>');

        $this->sut->handleCommand($command);
    }

    public function provider()
    {
        return [
            [
                'isVariation' => false,
                'isGoods' => true,
                'isRealUpgrade' => true,
                'isSpecialRestricted' => false,
                'event' => Cmd::ON_GRANT,
                'filename' => 'GV79 Application 111 Snapshot Grant.html',
                'description' => 'GV79 Application 111 Snapshot (at grant/valid)',
                'subCategory' => 14,
                'isExternal' => false,
            ],
            [
                'isVariation' => false,
                'isGoods' => false,
                'isRealUpgrade' => true,
                'isSpecialRestricted' => true,
                'event' => Cmd::ON_GRANT,
                'filename' => 'PSV356 Application 111 Snapshot Grant.html',
                'description' => 'PSV356 Application 111 Snapshot (at grant/valid)',
                'subCategory' => 14,
                'isExternal' => false,
            ],
            [
                'isVariation' => false,
                'isGoods' => false,
                'isRealUpgrade' => true,
                'isSpecialRestricted' => false,
                'event' => Cmd::ON_GRANT,
                'filename' => 'PSV421 Application 111 Snapshot Grant.html',
                'description' => 'PSV421 Application 111 Snapshot (at grant/valid)',
                'subCategory' => 14,
                'isExternal' => false,
            ],
            [
                'isVariation' => true,
                'isGoods' => true,
                'isRealUpgrade' => true,
                'isSpecialRestricted' => false,
                'event' => Cmd::ON_GRANT,
                'filename' => 'GV80A Application 111 Snapshot Grant.html',
                'description' => 'GV80A Application 111 Snapshot (at grant/valid)',
                'subCategory' => 14,
                'isExternal' => false,
            ],
            [
                'isVariation' => true,
                'isGoods' => true,
                'isRealUpgrade' => false,
                'isSpecialRestricted' => false,
                'event' => Cmd::ON_GRANT,
                'filename' => 'GV81 Application 111 Snapshot Grant.html',
                'description' => 'GV81 Application 111 Snapshot (at grant/valid)',
                'subCategory' => 14,
                'isExternal' => false,
            ],
            [
                'isVariation' => true,
                'isGoods' => false,
                'isRealUpgrade' => true,
                'isSpecialRestricted' => false,
                'event' => Cmd::ON_GRANT,
                'filename' => 'PSV431A Application 111 Snapshot Grant.html',
                'description' => 'PSV431A Application 111 Snapshot (at grant/valid)',
                'subCategory' => 14,
                'isExternal' => false,
            ],
            [
                'isVariation' => true,
                'isGoods' => false,
                'isRealUpgrade' => false,
                'isSpecialRestricted' => false,
                'event' => Cmd::ON_GRANT,
                'filename' => 'PSV431 Application 111 Snapshot Grant.html',
                'description' => 'PSV431 Application 111 Snapshot (at grant/valid)',
                'subCategory' => 14,
                'isExternal' => false,
            ],
            [
                'isVariation' => false,
                'isGoods' => true,
                'isRealUpgrade' => true,
                'isSpecialRestricted' => false,
                'event' => Cmd::ON_SUBMIT,
                'filename' => 'GV79 Application 111 Snapshot Submit.html',
                'description' => 'GV79 Application 111 Snapshot (at submission)',
                'subCategory' => 15,
                'isExternal' => true,
            ],
            [
                'isVariation' => false,
                'isGoods' => true,
                'isRealUpgrade' => true,
                'isSpecialRestricted' => false,
                'event' => Cmd::ON_REFUSE,
                'filename' => 'GV79 Application 111 Snapshot Refuse.html',
                'description' => 'GV79 Application 111 Snapshot (at refuse)',
                'subCategory' => 14,
                'isExternal' => false,
            ],
            [
                'isVariation' => false,
                'isGoods' => true,
                'isRealUpgrade' => true,
                'isSpecialRestricted' => false,
                'event' => Cmd::ON_WITHDRAW,
                'filename' => 'GV79 Application 111 Snapshot Withdraw.html',
                'description' => 'GV79 Application 111 Snapshot (at withdraw)',
                'subCategory' => 14,
                'isExternal' => false,
            ],
            [
                'isVariation' => false,
                'isGoods' => true,
                'isRealUpgrade' => true,
                'isSpecialRestricted' => false,
                'event' => Cmd::ON_NTU,
                'filename' => 'GV79 Application 111 Snapshot NTU.html',
                'description' => 'GV79 Application 111 Snapshot (at NTU)',
                'subCategory' => 14,
                'isExternal' => false,
            ],
        ];
    }
}
