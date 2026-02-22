<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva\Adapters;

use Common\RefData;
use Common\Service\Cqrs\Response;
use Common\Service\Lva\PeopleLvaService;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\VariationPeopleAdapter;

/**
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Controller\Lva\Adapters\VariationPeopleAdapter::class)]
class VariationPeopleAdapterTest extends MockeryTestCase
{
    public const APP_ID = 999;

    /** @var  VariationPeopleAdapter | m\MockInterface */
    protected $sut;

    /** @var  \Laminas\Form\Form | m\MockInterface */
    protected $mockForm;
    /** @var  \Common\Service\Table\TableBuilder | m\MockInterface */
    protected $mockTbl;
    /** @var  ContainerInterface | m\MockInterface */
    protected $mockContainer;
    /** @var  PeopleLvaService | m\MockInterface */
    protected $mockPplSrv;
    /** @var  \Laminas\Http\Response | m\MockInterface */
    protected $mockResp;

    public function setUp(): void
    {
        $this->mockForm = m::mock(\Laminas\Form\Form::class);
        $this->mockTbl = m::mock(\Common\Service\Table\TableBuilder::class);

        $this->mockPplSrv = m::mock(PeopleLvaService::class);

        $this->mockContainer = m::mock(ContainerInterface::class);
        $this->mockContainer->allows('get')->with('Table')->andReturn($this->mockTbl);

        $this->sut = m::mock(VariationPeopleAdapter::class, [$this->mockContainer, $this->mockPplSrv])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockResp = m::mock(Response::class);
        $this->mockResp->shouldReceive('isOk')->andReturn(true);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestCanModify')]
    public function testCanModify(bool $isExceptionalOrg, bool $expect): void
    {
        $this->sut->shouldReceive('isExceptionalOrganisation')->andReturn($isExceptionalOrg);

        static::assertEquals($expect, $this->sut->canModify());
    }

    /**
     * @return bool[][]
     *
     * @psalm-return list{array{isExceptionalOrg: true, expect: false}, array{isExceptionalOrg: false, expect: true}}
     */
    public static function dpTestCanModify(): array
    {
        return [
            [
                'isExceptionalOrg' => true,
                'expect' => false,
            ],
            [
                'isExceptionalOrg' => false,
                'expect' => true,
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetTableConfig')]
    public function testGetTableConfig(bool $useDeltas, string $expect): void
    {
        $this->mockTbl->shouldReceive('prepareTable')
            ->once()
            ->with($expect, ['unit_TableData']);

        $this->sut
            ->shouldReceive('getTableData')->andReturn(['unit_TableData'])
            ->shouldReceive('useDeltas')->andReturn($useDeltas);

        $this->sut->createTable();
    }

    /**
     * @return (bool|string)[][]
     *
     * @psalm-return list{array{useDeltas: false, expect: 'lva-people'}, array{useDeltas: true, expect: 'lva-variation-people'}}
     */
    public static function dpTestGetTableConfig(): array
    {
        return [
            [
                'useDeltas' => false,
                'expect' => 'lva-people',
            ],
            [
                'useDeltas' => true,
                'expect' => 'lva-variation-people',
            ],
        ];
    }

    public function testAlterFormForOrganisationCantModify(): void
    {
        $this->sut->shouldReceive('canModify')->andReturn(false);

        $this->mockPplSrv->shouldReceive('lockOrganisationForm')
            ->once()
            ->with($this->mockForm, $this->mockTbl);

        $this->mockTbl->shouldReceive('getAction')->never();

        $this->sut->alterFormForOrganisation($this->mockForm, $this->mockTbl);
    }

    public function testAlterFormForOrganisationCanModify(): void
    {
        $this->sut
            ->shouldReceive('canModify')->andReturn(true)
            ->shouldReceive('getOrganisationType')->andReturn(RefData::ORG_TYPE_REGISTERED_COMPANY);

        $this->mockPplSrv->shouldReceive('lockOrganisationForm')->never();

        $this->mockTbl
            ->shouldReceive('getAction')->once()->with('add')
            ->shouldReceive('removeAction')->once()->with('add')
            ->shouldReceive('addAction')->once();

        $this->sut->alterFormForOrganisation($this->mockForm, $this->mockTbl);
    }

    public function testAlterAddOrEditFormForOrganisationCanNotModify(): void
    {
        $this->mockPplSrv->shouldReceive('lockPersonForm')
            ->once()
            ->with($this->mockForm, 'unit_OrgType');

        $this->sut
            ->shouldReceive('canModify')->andReturn(false)
            ->shouldReceive('getOrganisationType')->andReturn('unit_OrgType');

        $this->sut->alterAddOrEditFormForOrganisation($this->mockForm);
    }

    public function testAlterAddOrEditFormForOrganisationCanModify(): void
    {
        $this->mockPplSrv->shouldReceive('lockPersonForm')->never();

        $this->sut->shouldReceive('canModify')->once()->andReturn(true);
        $this->sut->alterAddOrEditFormForOrganisation($this->mockForm);
    }

    public function testGetCreateCommand(): void
    {
        $this->sut
            ->shouldReceive('getApplicationId')->andReturn(self::APP_ID)
            ->shouldReceive('handleCommand')
            ->once()
            ->andReturnUsing(
                function (TransferCmd\Application\CreatePeople $cmd) {
                    static::assertEquals(self::APP_ID, $cmd->getId());
                    static::assertEquals('unit_familyName', $cmd->getFamilyName());

                    return $this->mockResp;
                }
            );

        $data = [
            'familyName' => 'unit_familyName',
        ];
        $this->sut->create($data);
    }

    public function testGetUpdateCommand(): void
    {
        $this->sut
            ->shouldReceive('getApplicationId')->andReturn(self::APP_ID)
            ->shouldReceive('handleCommand')
            ->once()
            ->andReturnUsing(
                function (TransferCmd\Application\UpdatePeople $cmd) {
                    static::assertEquals(self::APP_ID, $cmd->getId());
                    static::assertEquals('unit_familyName', $cmd->getFamilyName());
                    static::assertEquals(8001, $cmd->getPerson());

                    return $this->mockResp;
                }
            );

        $data = [
            'id' => 8001,
            'familyName' => 'unit_familyName',
        ];
        $this->sut->update($data);
    }

    public function testGetDeleteCommand(): void
    {
        $this->sut
            ->shouldReceive('getApplicationId')->andReturn(self::APP_ID)
            ->shouldReceive('handleCommand')
            ->once()
            ->andReturnUsing(
                function (TransferCmd\Application\DeletePeople $cmd) {
                    static::assertEquals(['unit_personIds'], $cmd->getPersonIds());

                    return $this->mockResp;
                }
            );

        $this->sut->delete(['unit_personIds']);
    }
}
