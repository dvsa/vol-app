<?php

namespace CommonTest\Common\Controller\Lva\Adapters;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\Adapters\AbstractPeopleAdapter;
use Common\Service\Cqrs\Response;
use Common\Service\Table\TableBuilder;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Form;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;

class AbstractPeopleAdapterTest extends MockeryTestCase
{
    protected const ID = 9001;

    protected const LIC_ID = 8001;

    /** @var  m\MockInterface | AbstractPeopleAdapter */
    private $sut;

    /** @var  m\MockInterface | AbstractPeopleAdapter */
    private $mockResp;

    private $container;

    #[\Override]
    protected function setUp(): void
    {
        $this->container = m::mock(ContainerInterface::class);
        $this->mockResp = m::mock(Response::class);
        $this->mockResp->shouldReceive('isOk')->andReturn(true);

        $this->sut = m::mock(AbstractPeopleAdapter::class, [$this->container])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sut->shouldReceive('handleQuery')->andReturn($this->mockResp);
    }

    public function testLoadPeopleDataLic(): void
    {
        $this->sut->shouldReceive('loadPeopleDataForLicence')->once()->with(self::ID);

        static::assertTrue($this->sut->loadPeopleData(AbstractController::LVA_LIC, self::ID));
    }

    public function testLoadPeopleDataOth(): void
    {
        $this->sut->shouldReceive('loadPeopleDataForApplication')->twice()->with(self::ID);

        static::assertTrue($this->sut->loadPeopleData(AbstractController::LVA_VAR, self::ID));
        static::assertTrue($this->sut->loadPeopleData(AbstractController::LVA_APP, self::ID));
    }

    public function testHasInforceLicences(): void
    {
        $this->mockResp->shouldReceive('getResult')->once()->andReturn(
            [
                'application' => [],
                'hasInforceLicences' => 'unit_EXPECT'
            ]
        );

        $this->sut->loadPeopleData(AbstractController::LVA_LIC, self::LIC_ID);

        static::assertEquals('unit_EXPECT', $this->sut->hasInforceLicences());
    }

    public function testIsExceptionalOrganisation(): void
    {
        $this->mockResp->shouldReceive('getResult')->once()->andReturn(
            [
                'application' => [],
                'isExceptionalType' => 'unit_EXPECT'
            ]
        );

        $this->sut->loadPeopleData(AbstractController::LVA_LIC, self::LIC_ID);

        static::assertEquals('unit_EXPECT', $this->sut->isExceptionalOrganisation());
    }

    public function testIsSoleTrader(): void
    {
        $this->mockResp->shouldReceive('getResult')->once()->andReturn(
            [
                'application' => [],
                'isSoleTrader' => 'unit_EXPECT'
            ]
        );

        $this->sut->loadPeopleData(AbstractController::LVA_LIC, self::LIC_ID);

        static::assertEquals('unit_EXPECT', $this->sut->isSoleTrader());
    }

    public function testIsPartnership(): void
    {
        $this->mockResp->shouldReceive('getResult')
            ->once()
            ->andReturn(
                [
                    'application' => [],
                    'organisation' => [
                        'type' => [
                            'id' => \Common\RefData::ORG_TYPE_PARTNERSHIP,
                        ],
                    ],
                ]
            );

        $this->sut->loadPeopleData(AbstractController::LVA_LIC, self::LIC_ID);

        static::assertTrue($this->sut->isPartnership());
    }

    /**
     * @dataProvider dpTestAlterFormForOrganisation
     */
    public function testAlterFormForOrganisation($type, $expected): void
    {
        $mockTable = m::mock(TableBuilder::class)
            ->shouldReceive('getAction')
            ->with('add')
            ->andReturn([])
            ->once()
            ->shouldReceive('removeAction')
            ->with('add')
            ->once()
            ->shouldReceive('addAction')
            ->with('add', ['label' => $expected])
            ->once()
            ->getMock();

        $this->sut->shouldReceive('getOrganisationType')
            ->andReturn($type)
            ->getMock();

        $this->sut->alterFormForOrganisation(m::mock(Form::class), $mockTable);
    }

    /**
     * @return string[][]
     *
     * @psalm-return array{ltd: list{'org_t_rc', 'lva.section.title.add_director'}, llp: list{'org_t_llp', 'lva.section.title.add_partner'}, partnership: list{'org_t_p', 'lva.section.title.add_partner'}, other: list{'org_t_pa', 'lva.section.title.add_person'}, irfo: list{'org_t_ir', 'lva.section.title.add_person'}}
     */
    public function dpTestAlterFormForOrganisation(): array
    {
        return [
            'ltd' => [
                \Common\RefData::ORG_TYPE_RC,
                'lva.section.title.add_director'
            ],
            'llp' => [
                \Common\RefData::ORG_TYPE_LLP,
                'lva.section.title.add_partner'
            ],
            'partnership' => [
                \Common\RefData::ORG_TYPE_PARTNERSHIP,
                'lva.section.title.add_partner'
            ],
            'other' => [
                \Common\RefData::ORG_TYPE_OTHER,
                'lva.section.title.add_person'
            ],
            'irfo' => [
                \Common\RefData::ORG_TYPE_IRFO,
                'lva.section.title.add_person'
            ]
        ];
    }

    public function testGetAddLabelTextForOrganisationReturnsNullIfNoOrganisationType(): void
    {
        $this->assertNull($this->sut->getAddLabelTextForOrganisation());
    }

    /**
     * @dataProvider dpTestAlterFormForOrganisation
     */
    public function testGetAddLabelTextForOrganisationReturnsAppropriateLabel($type, $expected): void
    {
        $this->sut->shouldReceive('getOrganisationType')
            ->andReturn($type)
            ->getMock();
        $this->assertEquals($expected, $this->sut->getAddLabelTextForOrganisation());
    }


    /**
     * @dataProvider  dpTestAlterFormForOrganisation
     */
    public function testAmendLicencePeopleListTableAltersTable($type, $expected): void
    {
        $settingsArray = [
            'actions' => [
                'add' => [
                    'label' => $expected
                ]
            ]
        ];

        $this->sut->shouldReceive('getOrganisationType')
            ->andReturn($type)
            ->getMock();

        $mockTable = m::mock(TableBuilder::class)
            ->shouldReceive('setSetting')
            ->with(
                'crud',
                $settingsArray
            )
            ->once()
            ->andReturnSelf()
            ->getMock();

        $this->sut->amendLicencePeopleListTable($mockTable);
    }

    public function testStatusesAreAddedToPeopleFromFlashMessenger(): void
    {
        $mockFM = m::mock(FlashMessenger::class);

        $this->sut
            ->shouldReceive('getController->plugin')
            ->with('FlashMessenger')
            ->andReturn($mockFM);

        $mockTableBuilder = m::mock(TableBuilder::class);

        $this->container
            ->shouldReceive('get')
            ->with('Table')
            ->andReturn($mockTableBuilder);

        $mockControllerPluginManager = m::mock();

        $this->container
            ->shouldReceive('get')
            ->with('ControllerPluginManager')
            ->andReturn($mockControllerPluginManager);

        $mockControllerPluginManager->shouldReceive('get')->andReturn($mockFM);

        $mockFM
            ->shouldReceive('getMessages')
            ->with(AbstractController::FLASH_MESSENGER_CREATED_PERSON_NAMESPACE)
            ->andReturn([53]);

        $this->sut
            ->shouldReceive('formatTableData')
            ->andReturn(
                [
                    [
                        'id' => 39
                    ],
                    [
                        'id' => 53
                    ]
                ]
            );

        $expected = [
            [
                'id' => 39,
                'status' => null
            ],
            [
                'id' => 53,
                'status' => 'new'
            ]
        ];

        $mockTableBuilder
            ->shouldReceive('prepareTable')
            ->andReturnUsing(
                function ($tableConfig, $tableData) use ($expected) {
                    $this->assertSame($expected, $tableData);
                    return m::mock(TableBuilder::class);
                }
            );

        $this->sut->createTable();
    }
}
