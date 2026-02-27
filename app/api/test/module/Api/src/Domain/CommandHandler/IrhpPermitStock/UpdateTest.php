<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitStock;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitStock\Update as UpdateHandler;
use Dvsa\Olcs\Api\Domain\Repository\Country as CountryRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as PermitStockRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitType as PermitTypeRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\System\IrhpPermitStock as PermitStockEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\IrhpPermitStock\Update as UpdateCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Update IrhpPermitStock Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateHandler();
        $this->mockRepo('Country', CountryRepo::class);
        $this->mockRepo('IrhpPermitStock', PermitStockRepo::class);
        $this->mockRepo('IrhpPermitType', PermitTypeRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            RefData::PERMIT_CAT_HORS_CONTINGENT,
        ];
        $this->references = [
            Country::class => [
                Country::ID_MOROCCO => m::mock(Country::class),
            ],
            IrhpPermitType::class => [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT => m::mock(IrhpPermitType::class),
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL => m::mock(IrhpPermitType::class),
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $id = 1;
        $permitType = IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT;
        $validFrom = '2119-01-01';
        $validTo = '2119-01-01';
        $initialStock = '1500';

        $cmdData = [
            'irhpPermitType' => $permitType,
            'validFrom' => $validFrom,
            'validTo' => $validTo,
            'initialStock' => $initialStock
        ];

        $command = UpdateCmd::create($cmdData);

        $entity = m::mock(PermitStockEntity::class);

        $entity->shouldReceive('update')
            ->with(
                $this->references[IrhpPermitType::class][IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT],
                null,
                null,
                $initialStock,
                null,
                $validFrom,
                $validTo,
                null
            )
            ->once()
            ->andReturn(m::mock(IrhpPermitStock::class));

        $entity->shouldReceive('getId')
            ->twice()
            ->andReturn($id);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($entity);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('save')
            ->once()
            ->with($entity);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['Irhp Permit Stock' => $id],
            'messages' => ["Irhp Permit Stock '" . $id . "' updated"]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandBilateral(): void
    {
        $id = 1;
        $permitType = IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL;
        $validFrom = '2119-01-01';
        $validTo = '2119-01-01';
        $initialStock = '1500';
        $periodNameKey = 'period.name.translation.key';
        $hiddenSs = true;

        $cmdData = [
            'irhpPermitType' => $permitType,
            'country' => Country::ID_MOROCCO,
            'permitCategory' => RefData::PERMIT_CAT_HORS_CONTINGENT,
            'validFrom' => $validFrom,
            'validTo' => $validTo,
            'initialStock' => $initialStock,
            'periodNameKey' => $periodNameKey,
            'hiddenSs' => $hiddenSs,
        ];

        $command = UpdateCmd::create($cmdData);

        $entity = m::mock(PermitStockEntity::class);

        $entity->shouldReceive('update')
            ->with(
                $this->references[IrhpPermitType::class][IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL],
                $this->references[Country::class][Country::ID_MOROCCO],
                $this->refData[RefData::PERMIT_CAT_HORS_CONTINGENT],
                $initialStock,
                $periodNameKey,
                $validFrom,
                $validTo,
                $hiddenSs
            )
            ->once()
            ->andReturn(m::mock(IrhpPermitStock::class));

        $entity->shouldReceive('getId')
            ->twice()
            ->andReturn($id);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($entity);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('save')
            ->once()
            ->with($entity);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['Irhp Permit Stock' => $id],
            'messages' => ["Irhp Permit Stock '" . $id . "' updated"]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
