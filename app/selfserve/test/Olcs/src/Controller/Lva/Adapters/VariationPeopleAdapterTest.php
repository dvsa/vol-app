<?php

namespace OlcsTest\Controller\Lva\Adapters;

use Common\RefData;
use Common\Service\Lva\PeopleLvaService;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\VariationPeopleAdapter;

/**
 * @covers \Olcs\Controller\Lva\Adapters\VariationPeopleAdapter
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VariationPeopleAdapterTest extends MockeryTestCase
{
    const APP_ID = 999;

    /** @var  VariationPeopleAdapter | m\MockInterface */
    protected $sut;

    /** @var  \Zend\Form\Form | m\MockInterface */
    protected $mockForm;
    /** @var  \Common\Service\Table\TableBuilder | m\MockInterface */
    protected $mockTbl;
    /** @var  \Zend\ServiceManager\ServiceManager | m\MockInterface */
    protected $mockSm;
    /** @var  PeopleLvaService | m\MockInterface */
    protected $mockPplSrv;
    /** @var  \Zend\Http\Response | m\MockInterface */
    protected $mockResp;

    public function setUp()
    {
        $this->mockForm = m::mock(\Zend\Form\Form::class);
        $this->mockTbl = m::mock(\Common\Service\Table\TableBuilder::class);

        $this->mockPplSrv = m::mock(PeopleLvaService::class);

        $this->mockSm = m::mock(\Zend\ServiceManager\ServiceManager::class)->makePartial();
        $this->mockSm
            ->setAllowOverride(true)
            ->setService('Table', $this->mockTbl)
            ->setService('Lva\People', $this->mockPplSrv);

        $this->sut = m::mock(VariationPeopleAdapter::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->sut->setServiceLocator($this->mockSm);

        $this->mockResp = m::mock(\Zend\Http\Response::class);
        $this->mockResp->shouldReceive('isOk')->andReturn(true);
    }

    /**
     * @dataProvider dpTestCanModify
     */
    public function testCanModify($isExceptionalOrg, $expect)
    {
        $this->sut->shouldReceive('isExceptionalOrganisation')->andReturn($isExceptionalOrg);

        static::assertEquals($expect, $this->sut->canModify());
    }

    public function dpTestCanModify()
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

    /**
     * @dataProvider dpTestGetTableConfig
     */
    public function testGetTableConfig($useDeltas, $expect)
    {
        $this->mockTbl->shouldReceive('prepareTable')
            ->once()
            ->with($expect, ['unit_TableData']);

        $this->sut
            ->shouldReceive('getTableData')->andReturn(['unit_TableData'])
            ->shouldReceive('useDeltas')->andReturn($useDeltas);

        $this->sut->createTable();
    }

    public function dpTestGetTableConfig()
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

    public function testAlterFormForOrganisationCantModify()
    {
        $this->sut->shouldReceive('canModify')->andReturn(false);

        $this->mockPplSrv->shouldReceive('lockOrganisationForm')
            ->once()
            ->with($this->mockForm, $this->mockTbl);

        $this->mockTbl->shouldReceive('getAction')->never();

        $this->sut->alterFormForOrganisation($this->mockForm, $this->mockTbl);
    }

    public function testAlterFormForOrganisationCanModify()
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

    public function testAlterAddOrEditFormForOrganisationCanNotModify()
    {
        $this->mockPplSrv->shouldReceive('lockPersonForm')
            ->once()
            ->with($this->mockForm, 'unit_OrgType');

        $this->sut
            ->shouldReceive('canModify')->andReturn(false)
            ->shouldReceive('getOrganisationType')->andReturn('unit_OrgType');

        $this->sut->alterAddOrEditFormForOrganisation($this->mockForm);
    }

    public function testAlterAddOrEditFormForOrganisationCanModify()
    {
        $this->mockPplSrv->shouldReceive('lockPersonForm')->never();

        $this->sut->shouldReceive('canModify')->once()->andReturn(true);
        $this->sut->alterAddOrEditFormForOrganisation($this->mockForm);
    }

    public function testGetCreateCommand()
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

    public function testGetUpdateCommand()
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

    public function testGetDeleteCommand()
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
