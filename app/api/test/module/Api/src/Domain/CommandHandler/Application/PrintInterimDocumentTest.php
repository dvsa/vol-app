<?php

declare(strict_types=1);

/**
 * Print Interim Document Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\PrintInterimDocument;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Application\PrintInterimDocument as Cmd;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Print Interim Document Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PrintInterimDocumentTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PrintInterimDocument();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommand')]
    public function testHandleCommand(mixed $vehicleType, mixed $isVariation, mixed $expectedData): void
    {
        $command = Cmd::create(['id' => 111]);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setLicence($licence);
        $application->setIsVariation($isVariation);
        $application->setVehicleType(new RefData($vehicleType));

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $result1 = new Result();
        $result1->addMessage('GenerateAndStore');
        $this->expectedSideEffect(GenerateAndStore::class, $expectedData, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'GenerateAndStore'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function dpHandleCommand(): array
    {
        return [
            'mixed fleet application' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'isVariation' => false,
                'expectedData' => [
                    'template' => 'GV_INT_LICENCE_V1',
                    'query' => ['application' => 111, 'licence' => 222],
                    'description' => 'GV Interim Licence',
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
                ],
            ],
            'lgv application' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'isVariation' => false,
                'expectedData' => [
                    'template' => 'GV_LGV_INT_LICENCE_V1',
                    'query' => ['application' => 111, 'licence' => 222],
                    'description' => 'GV Interim Licence LGV Only',
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
                ],
            ],
            'mixed fleet variation' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'isVariation' => true,
                'expectedData' => [
                    'template' => 'GV_INT_DIRECTION_V1',
                    'query' => ['application' => 111, 'licence' => 222],
                    'description' => 'GV Interim Direction',
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
                ],
            ],
            'lgv variation' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'isVariation' => true,
                'expectedData' => [
                    'template' => 'GV_LGV_INT_DIRECTION_V1',
                    'query' => ['application' => 111, 'licence' => 222],
                    'description' => 'GV Interim Direction LGV Only',
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
                ],
            ],
        ];
    }
}
