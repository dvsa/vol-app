<?php

declare(strict_types=1);

/**
 * Create Goods Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\CreateVehicleListDocument;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVehicleListDocument as Cmd;
use Dvsa\Olcs\Api\Entity\System\Category;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Create Goods Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateVehicleListDocumentTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateVehicleListDocument();
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    /**
     * @param $isDp
     * @param $isNi
     * @param $expectedTempateId
     * @param $expectedTemplateDescription
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestHandleCommandAll')]
    public function testHandleCommandAll(mixed $isDp, mixed $isNi, mixed $expectedTempateId, mixed $expectedTemplateDescription): void
    {
        $data = [
            'id' => 111,
            'user' => 1
        ];
        if ($isDp) {
            $data['type'] = 'dp';
        }

        $command = Cmd::create($data);

        $licence = m::mock();
        $licence->shouldReceive('isNi')->once()->with()->andReturn($isNi);
        $this->repoMap['Licence']->shouldReceive('fetchById')->with(111)->once()->andReturn($licence);

        $data = [
            'documentId' => 123,
            'jobName' => $expectedTemplateDescription,
            'user' => 1
        ];
        $result1 = new Result();
        $this->expectedSideEffect(Enqueue::class, $data, $result1);

        $data = [
            'template' => $expectedTempateId,
            'query' => ['licence' => 111, 'user' => 1],
            'licence' => 111,
            'description' => $expectedTemplateDescription,
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_LICENCE_VEHICLE_LIST,
            'isExternal' => false,
            'application' => null,
            'busReg' => null,
            'case' => null,
            'irfoOrganisation' => null,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => null,
            'operatingCentre' => null,
            'opposition' => null,
            'isScan' => 0,
            'issuedDate' => null
        ];
        $result2 = new Result();
        $result2->addId('document', 123);
        $this->expectedSideEffect(GenerateAndStore::class, $data, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'document' => 123
            ],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function dataProviderTestHandleCommandAll(): array
    {
        return [
            // is DP type, is NI, Expected description, Expected template ID,
            [true, true, Document::GV_DISC_LETTER_NI, 'New disc notification'],
            [true, false, Document::GV_DISC_LETTER_GB, 'New disc notification'],
            [false, true, Document::GV_VEHICLE_LIST_NI, 'Goods Vehicle List'],
            [false, false, Document::GV_VEHICLE_LIST_GB, 'Goods Vehicle List'],
        ];
    }
}
