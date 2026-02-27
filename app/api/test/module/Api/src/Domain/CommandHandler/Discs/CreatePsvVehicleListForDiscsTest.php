<?php

declare(strict_types=1);

/**
 * Create PSV vehicle list for discs test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Discs;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Discs\CreatePsvVehicleListForDiscs;
use Dvsa\Olcs\Api\Domain\Command\Discs\CreatePsvVehicleListForDiscs as Cmd;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Create PSV vehicle list for discs test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreatePsvVehicleListForDiscsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreatePsvVehicleListForDiscs();
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 1,
            'knownValues' => 'knownValues',
            'user' => 2
        ];

        $command = Cmd::create($data);

        $printData = [
            'documentId' => 'id1',
            'jobName' => 'New disc notification',
            'user' => 2
        ];

        $this->expectedSideEffect(Enqueue::class, $printData, new Result());

        $createDocData = [
            'template' => 'PSVVehiclesList',
            'query' => [
                'licence' => 1,
                'user' => 2
            ],
            'irfoOrganisation' => null,
            'submission' => null,
            'trafficArea' => null,
            'operatingCentre' => null,
            'opposition' => null,
            'category'      => Category::CATEGORY_LICENSING,
            'subCategory'   => Category::DOC_SUB_CATEGORY_LICENCE_VEHICLE_LIST,
            'description'   => 'New disc notification',
            'isExternal'    => false,
            'isScan' => 0,
            'issuedDate' => null,
            'application' => null,
            'busReg' => null,
            'case' => null,
            'transportManager' => null,
            'licence'       => 1
        ];

        $createDocResult = new Result();
        $createDocResult->addId('document', 'id1');
        $createDocResult->addMessage('message');
        $this->expectedSideEffect(GenerateAndStore::class, $createDocData, $createDocResult);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'document' => 'id1'
            ],
            'messages' => [
                'message'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }
}
