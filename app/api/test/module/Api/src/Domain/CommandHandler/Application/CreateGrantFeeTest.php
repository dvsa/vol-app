<?php

declare(strict_types=1);

/**
 * Create Grant Fee Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\CreateApplicationFee;
use Dvsa\Olcs\Api\Domain\Command\Document\DispatchDocument;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\CreateGrantFee;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateGrantFee as Cmd;

/**
 * Create Grant Fee Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateGrantFeeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateGrantFee();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111
        ];

        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setLicence($licence);
        $application
            ->shouldReceive('hasOutstandingGrantFee')
            ->andReturn(false);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $params = [
            'fee' => 123,
            'application' => 111,
            'licence' => 222
        ];

        $dtoData = ['id' => 111, 'feeTypeFeeType' => FeeType::FEE_TYPE_GRANT, 'description' => 'Grant fee due'];
        $result1 = new Result();
        $result1->addId('fee', 123);
        $result1->addMessage('CreateApplicationFee');
        $this->expectedSideEffect(CreateApplicationFee::class, $dtoData, $result1);

        $docData = [
            'template' => 'FEE_REQ_GRANT_GV',
            'query' => $params,
            'description' => 'Goods Grant Fee Request',
            'application' => 111,
            'licence'     => 222,
            'category'    => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_FEE_REQUEST,
            'isExternal'  => false,
            'busReg' => null,
            'case' => null,
            'irfoOrganisation' => null,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => null,
            'operatingCentre' => null,
            'opposition' => null,
            'isScan' => 0,
            'issuedDate' => null,
            'metadata' => null
        ];
        $result2 = new Result();
        $result2->addMessage('GenerateAndStore');
        $this->expectedSideEffect(GenerateAndStore::class, $docData, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'fee' => 123
            ],
            'messages' => [
                'CreateApplicationFee',
                'GenerateAndStore'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandExistingGrantFee(): void
    {
        $data = [
            'id' => 111
        ];

        $command = Cmd::create($data);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application
            ->shouldReceive('hasOutstandingGrantFee')
            ->andReturn(true);

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $result = $this->sut->handleCommand($command);

        $expected = [
            // no-op
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
