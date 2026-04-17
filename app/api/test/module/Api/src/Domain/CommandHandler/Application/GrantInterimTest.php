<?php

declare(strict_types=1);

/**
 * Grant Interim Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\InForceInterim;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\GrantInterim;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\Category;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Application\GrantInterim as Cmd;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateApplicationFee as CreateApplicationFeeCmd;

/**
 * Grant Interim Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GrantInterimTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GrantInterim();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('Fee', \Dvsa\Olcs\Api\Domain\Repository\Fee::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['id' => 111]);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setIsVariation(false);

        $fees = [];

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['Fee']->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(111, true)
            ->andReturn($fees)
            ->once()
            ->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(111, false, true)
            ->andReturn(['fees'])
            ->once()
            ->shouldReceive('fetchFeeByTypeAndApplicationId')
            ->with(FeeType::FEE_TYPE_VAR, 111)
            ->andReturn([])
            ->once()
            ->getMock();

        $result1 = new Result();
        $result1->addMessage('InForceInterim');
        $this->expectedSideEffect(InForceInterim::class, ['id' => 111], $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'action' => 'in_force'
            ],
            'messages' => [
                'InForceInterim'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithFees(): void
    {
        $command = Cmd::create(['id' => 111]);

        $expectedQuery = [
            'application' => 111,
            'licence' => 222,
            'fee' => 444
        ];

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setIsVariation(false);
        $application->setLicence($licence);

        /** @var Fee $fee */
        $fee = m::mock(Fee::class)->makePartial();
        $fee->setId(444);

        $fees = [$fee];

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->with($application);

        $this->repoMap['Fee']->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(111, true)
            ->andReturn($fees)
            ->once()
            ->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(111, false, true)
            ->andReturn([])
            ->once()
            ->shouldReceive('fetchFeeByTypeAndApplicationId')
            ->with(FeeType::FEE_TYPE_VAR, 111)
            ->andReturn([])
            ->once()
            ->getMock();

        $expectedData = [
            'template' => 'FEE_REQ_INT_APP',
            'query' => $expectedQuery,
            'description' => 'GV Interim licence fee request',
            'application' => 111,
            'licence' => 222,
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS,
            'isExternal' => false,
            'isScan' => false,
            'busReg' => null,
            'case' => null,
            'irfoOrganisation' => null,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => null,
            'operatingCentre' => null,
            'opposition' => null,
            'issuedDate' => null,
            'dispatch' => true
        ];

        $result1 = new Result();
        $result1->addMessage('GenerateAndStore');
        $this->expectedSideEffect(GenerateAndStore::class, $expectedData, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'action' => 'fee_request'
            ],
            'messages' => [
                'GenerateAndStore',
                'Interim status updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithFeesVariation(): void
    {
        $command = Cmd::create(['id' => 111]);

        $expectedQuery = [
            'application' => 111,
            'licence' => 222,
            'fee' => 444
        ];

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setIsVariation(true);
        $application->setLicence($licence);

        /** @var Fee $fee */
        $fee = m::mock(Fee::class)->makePartial();
        $fee->setId(444);

        $fees = [$fee];

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->with($application);

        $this->repoMap['Fee']->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(111, true)
            ->andReturn($fees)
            ->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(111, false, true)
            ->andReturn([])
            ->once()
            ->shouldReceive('fetchFeeByTypeAndApplicationId')
            ->with(FeeType::FEE_TYPE_VAR, 111)
            ->andReturn([])
            ->once()
            ->getMock();

        $expectedData = [
            'template' => 'FEE_REQ_INT_APP',
            'query' => $expectedQuery,
            'description' => 'GV Interim direction fee request',
            'application' => 111,
            'licence' => 222,
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS,
            'isExternal' => false,
            'isScan' => false,
            'busReg' => null,
            'case' => null,
            'irfoOrganisation' => null,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => null,
            'operatingCentre' => null,
            'opposition' => null,
            'issuedDate' => null,
            'dispatch' => true
        ];

        $result1 = new Result();
        $result1->addMessage('GenerateAndStore');
        $this->expectedSideEffect(GenerateAndStore::class, $expectedData, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'action' => 'fee_request'
            ],
            'messages' => [
                'GenerateAndStore',
                'Interim status updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandNoFees(): void
    {
        $command = Cmd::create(['id' => 111]);

        $expectedQuery = [
            'application' => 111,
            'licence' => 222,
            'fee' => 444
        ];

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setLicence($licence);
        $application->setIsVariation(false);

        /** @var Fee $fee */
        $fee = m::mock(Fee::class)->makePartial();
        $fee->setId(444);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->once()
            ->shouldReceive('save')
            ->with($application)
            ->once();

        $this->repoMap['Fee']->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(111, true)
            ->andReturn([])
            ->once()
            ->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(111, false, true)
            ->andReturn([])
            ->once()
            ->shouldReceive('fetchById')
            ->with(444)
            ->once()
            ->andReturn($fee)
            ->shouldReceive('fetchFeeByTypeAndApplicationId')
            ->with(FeeType::FEE_TYPE_VAR, 111)
            ->andReturn([])
            ->once()
            ->getMock();

        $result1 = new Result();
        $result1->addId('fee', 444);
        $this->expectedSideEffect(
            CreateApplicationFeeCmd::class,
            ['id' => 111, 'feeTypeFeeType' => FeeType::FEE_TYPE_GRANTINT],
            $result1
        );

        $expectedGenerate = [
            'template' => 'FEE_REQ_INT_APP',
            'query' => $expectedQuery,
            'description' => 'GV Interim licence fee request',
            'application' => 111,
            'licence' => 222,
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS,
            'isExternal' => false,
            'isScan' => false,
            'busReg' => null,
            'case' => null,
            'irfoOrganisation' => null,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => null,
            'operatingCentre' => null,
            'opposition' => null,
            'issuedDate' => null,
            'dispatch' => true
        ];

        $result2 = new Result();
        $result2->addMessage('GenerateAndStore');
        $this->expectedSideEffect(GenerateAndStore::class, $expectedGenerate, $result2);

        $expected = [
            'id' => ['fee' => 444, 'action' => 'fee_request'],
            'messages' => ['GenerateAndStore', 'Interim status updated']
        ];
        $result = $this->sut->handleCommand($command);

        $this->assertEquals($expected, $result->toArray());
    }
}
