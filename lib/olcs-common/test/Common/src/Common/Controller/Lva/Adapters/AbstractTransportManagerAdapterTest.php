<?php

namespace CommonTest\Common\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\AbstractTransportManagerAdapter;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Abstract Transport Manager Adapter Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class AbstractTransportManagerAdapterTest extends MockeryTestCase
{
    /** @var  \CommonTest\Common\Controller\Lva\Adapters\StubAbstractTransportManagerAdapter */
    protected $sut;

    /** @var  ContainerInterface|\Mockery\MockInterface */
    protected $container;

    #[\Override]
    protected function setUp(): void
    {
        $this->container = m::mock(ContainerInterface::class);

        /** @var TransferAnnotationBuilder $mockAnnotationBuilder */
        $mockAnnotationBuilder = m::mock(TransferAnnotationBuilder::class);
        /** @var CachingQueryService $mockQuerySrv */
        $mockQuerySrv = m::mock(CachingQueryService::class);
        /** @var CommandService $mockCommandSrv */
        $mockCommandSrv = m::mock(CommandService::class);

        $this->sut = new StubAbstractTransportManagerAdapter(
            $mockAnnotationBuilder,
            $mockQuerySrv,
            $mockCommandSrv,
            $this->container
        );
    }

    public function testGetNumberOfRows(): void
    {
        $this->assertEquals(2, $this->sut->getNumberOfRows(888, 999));
    }

    public function testGetTable(): void
    {
        $mockTable = m::mock(\stdClass::class);
        $this->container->shouldReceive('get->prepareTable')->once()->with('template')->andReturn($mockTable);

        static::assertEquals($mockTable, $this->sut->getTable('template'));
    }

    public function testMustHaveAtLeastOneTm(): void
    {
        static::assertFalse($this->sut->mustHaveAtLeastOneTm());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAddMessages(): void
    {
        // no assertion as its a no op
        $this->sut->addMessages(99);
    }

    /**
     * @dataProvider dataProviderTestMapResultForTable
     */
    public function testMapResultForTable($licTms, $appTms, $expect): void
    {
        $actual = $this->sut->mapResultForTable($appTms, $licTms);

        static::assertEquals($expect, $actual);
    }

    /**
     * @return ((int|string)[]|int|null|string)[][][][]
     *
     * @psalm-return list{array{licTms: list{array{id: 201, tmid: 70001, birthDate: 'unit_BirthDate', forename: 'unit_Forename', familyName: 'unit_FamilyName', emailAddress: 'unit_LicEmail'}}, appTms: list{array{id: 101, action: 'unit_Action', tmid: 80001, tmasid: 'unit_TmAppStatusId', tmasdesc: 'unit_TmAppStatusDesc', birthDate: 'unit_BirthDate', forename: 'unit_Forename', familyName: 'unit_FamilyName', emailAddress: 'unit_AppEmail'}}, expect: array{80001a: array{id: 101, name: array{familyName: 'unit_FamilyName', forename: 'unit_Forename'}, status: array{id: 'unit_TmAppStatusId', description: 'unit_TmAppStatusDesc'}, email: 'unit_AppEmail', dob: 'unit_BirthDate', transportManager: array{id: 80001}, action: 'unit_Action'}, 70001: array{id: 'L201', name: array{familyName: 'unit_FamilyName', forename: 'unit_Forename'}, status: null, email: 'unit_LicEmail', dob: 'unit_BirthDate', transportManager: array{id: 70001}, action: 'E'}}}, array{licTms: list{array{id: 301, tmid: 8888, birthDate: 'unit_BirthDate', forename: 'unit_Forename', familyName: 'unit_FamilyName', emailAddress: 'unit_LicEmail'}}, appTms: list{array{id: 101, tmid: 8888, tmasid: 'unit_TmAppStatusId', tmasdesc: 'unit_TmAppStatusDesc', birthDate: 'unit_BirthDate', forename: 'unit_Forename', familyName: 'unit_FamilyName', emailAddress: 'unit_AppEmail', action: 'U'}}, expect: array{8888: array{id: 'L301', name: array{familyName: 'unit_FamilyName', forename: 'unit_Forename'}, status: null, email: 'unit_LicEmail', dob: 'unit_BirthDate', transportManager: array{id: 8888}, action: 'C'}, 8888a: array{id: 101, name: array{familyName: 'unit_FamilyName', forename: 'unit_Forename'}, status: array{id: 'unit_TmAppStatusId', description: 'unit_TmAppStatusDesc'}, email: 'unit_AppEmail', dob: 'unit_BirthDate', transportManager: array{id: 8888}, action: 'U'}}}, array{licTms: list{array{id: 301, tmid: 8888, birthDate: 'unit_BirthDate', forename: 'unit_Forename', familyName: 'unit_FamilyName', emailAddress: 'unit_LicEmail'}}, appTms: list{array{id: 101, tmid: 8888, tmasid: 'unit_TmAppStatusId', tmasdesc: 'unit_TmAppStatusDesc', birthDate: 'unit_BirthDate', forename: 'unit_Forename', familyName: 'unit_FamilyName', emailAddress: 'unit_AppEmail', action: 'D'}}, expect: array{8888a: array{id: 101, name: array{familyName: 'unit_FamilyName', forename: 'unit_Forename'}, status: array{id: 'unit_TmAppStatusId', description: 'unit_TmAppStatusDesc'}, email: 'unit_AppEmail', dob: 'unit_BirthDate', transportManager: array{id: 8888}, action: 'D'}}}}
     */
    public function dataProviderTestMapResultForTable(): array
    {
        $expectedName = [
            'familyName' => 'unit_FamilyName',
            'forename' => 'unit_Forename',
        ];
        $expectedStatus = [
            'id' => 'unit_TmAppStatusId',
            'description' => 'unit_TmAppStatusDesc'
        ];
        return [
            [
                'licTms' => [
                    [
                        'id' => 201,
                        'tmid' => 70001,
                        'birthDate' => 'unit_BirthDate',
                        'forename' => 'unit_Forename',
                        'familyName' => 'unit_FamilyName',
                        'emailAddress' => 'unit_LicEmail'
                    ],
                ],
                'appTms' => [
                    [
                        'id' => 101,
                        'action' => 'unit_Action',
                        'tmid' => 80001,
                        'tmasid' => 'unit_TmAppStatusId',
                        'tmasdesc' => 'unit_TmAppStatusDesc',
                        'birthDate' => 'unit_BirthDate',
                        'forename' => 'unit_Forename',
                        'familyName' => 'unit_FamilyName',
                        'emailAddress' => 'unit_AppEmail'
                    ],
                ],
                'expect' => [
                    '80001a' => [
                        'id' => 101,
                        'name' => $expectedName,
                        'status' => $expectedStatus,
                        'email' => 'unit_AppEmail',
                        'dob' => 'unit_BirthDate',
                        'transportManager' => ['id' => 80001],
                        'action' => 'unit_Action',
                    ],
                    '70001' => [
                        'id' => 'L201',
                        'name' => $expectedName,
                        'status' => null,
                        'email' => 'unit_LicEmail',
                        'dob' => 'unit_BirthDate',
                        'transportManager' => ['id' => 70001],
                        'action' => 'E',
                    ],
                ],
            ],
            //  test status of existing manager in licence is changed to 'C'
            [
                'licTms' => [
                    [
                        'id' => 301,
                        'tmid' => 8888,
                        'birthDate' => 'unit_BirthDate',
                        'forename' => 'unit_Forename',
                        'familyName' => 'unit_FamilyName',
                        'emailAddress' => 'unit_LicEmail'
                    ],
                ],
                'appTms' => [
                    [
                        'id' => 101,
                        'tmid' => 8888,
                        'tmasid' => 'unit_TmAppStatusId',
                        'tmasdesc' => 'unit_TmAppStatusDesc',
                        'birthDate' => 'unit_BirthDate',
                        'forename' => 'unit_Forename',
                        'familyName' => 'unit_FamilyName',
                        'emailAddress' => 'unit_AppEmail',
                        'action' => 'U'
                    ],
                ],
                'expect' => [
                    '8888' => [
                        'id' => 'L301',
                        'name' => $expectedName,
                        'status' => null,
                        'email' => 'unit_LicEmail',
                        'dob' => 'unit_BirthDate',
                        'transportManager' => ['id' => 8888],
                        'action' => 'C',
                    ],
                    '8888a' => [
                        'id' => 101,
                        'name' => $expectedName,
                        'status' => $expectedStatus,
                        'email' => 'unit_AppEmail',
                        'dob' => 'unit_BirthDate',
                        'transportManager' => ['id' => 8888],
                        'action' => 'U',
                    ],
                ],
            ],
            //  test remove original if status of manager in application is 'D'
            [
                'licTms' => [
                    [
                        'id' => 301,
                        'tmid' => 8888,
                        'birthDate' => 'unit_BirthDate',
                        'forename' => 'unit_Forename',
                        'familyName' => 'unit_FamilyName',
                        'emailAddress' => 'unit_LicEmail'
                    ],
                ],
                'appTms' => [
                    [
                        'id' => 101,
                        'tmid' => 8888,
                        'tmasid' => 'unit_TmAppStatusId',
                        'tmasdesc' => 'unit_TmAppStatusDesc',
                        'birthDate' => 'unit_BirthDate',
                        'forename' => 'unit_Forename',
                        'familyName' => 'unit_FamilyName',
                        'emailAddress' => 'unit_AppEmail',
                        'action' => 'D'
                    ],
                ],
                'expect' => [
                    '8888a' => [
                        'id' => 101,
                        'name' => $expectedName,
                        'status' => $expectedStatus,
                        'email' => 'unit_AppEmail',
                        'dob' => 'unit_BirthDate',
                        'transportManager' => ['id' => 8888],
                        'action' => 'D',
                    ],
                ],
            ]
        ];
    }

    /**
     * @dataProvider dataProviderTestSortResultForTable
     */
    public function testSortResultForTable($method, $data, $expect): void
    {
        $actual = $this->sut->sortResultForTable($data, $method);

        static::assertEquals($expect, $actual);
    }

    /**
     * @return ((string|string[])[][]|int)[][]
     *
     * @psalm-return list{array{method: 1, data: list{array{name: array{familyName: 'Xlast', forename: 'Afirst'}}, array{name: array{familyName: 'Alast', forename: 'Bfirst'}}, array{name: array{familyName: 'Alast', forename: 'Cfirst'}}}, expect: list{array{name: array{familyName: 'Alast', forename: 'Bfirst'}}, array{name: array{familyName: 'Alast', forename: 'Cfirst'}}, array{name: array{familyName: 'Xlast', forename: 'Afirst'}}}}, array{method: 2, data: list{array{name: array{familyName: 'Xlast', forename: 'Afirst'}, action: 'A'}, array{name: array{familyName: 'Alast', forename: 'Bfirst'}, action: 'A'}, array{name: array{familyName: 'Alast', forename: 'Cfirst'}, action: 'X'}}, expect: list{array{name: array{familyName: 'Alast', forename: 'Cfirst'}, action: 'X'}, array{name: array{familyName: 'Alast', forename: 'Bfirst'}, action: 'A'}, array{name: array{familyName: 'Xlast', forename: 'Afirst'}, action: 'A'}}}}
     */
    public function dataProviderTestSortResultForTable(): array
    {
        return [
            //  test sorting method by Last and First name
            [
                'method' => AbstractTransportManagerAdapter::SORT_LAST_FIRST_NAME,
                'data' => [
                    [
                        'name' => [
                            'familyName' => 'Xlast',
                            'forename' => 'Afirst',
                        ],
                    ],
                    [
                        'name' => [
                            'familyName' => 'Alast',
                            'forename' => 'Bfirst',
                        ],
                    ],
                    [
                        'name' => [
                            'familyName' => 'Alast',
                            'forename' => 'Cfirst',
                        ],
                    ],
                ],
                'expect' => [
                    [
                        'name' => [
                            'familyName' => 'Alast',
                            'forename' => 'Bfirst',
                        ],
                    ],
                    [
                        'name' => [
                            'familyName' => 'Alast',
                            'forename' => 'Cfirst',
                        ],
                    ],
                    [
                        'name' => [
                            'familyName' => 'Xlast',
                            'forename' => 'Afirst',
                        ],
                    ],
                ],
            ],
            //  test sorting method by Last and First name, and all new items to the end and sorted by add date
            [
                'method' => AbstractTransportManagerAdapter::SORT_LAST_FIRST_NAME_NEW_AT_END,
                'data' => [
                    [
                        'name' => [
                            'familyName' => 'Xlast',
                            'forename' => 'Afirst',
                        ],
                        'action' => 'A',
                    ],
                    [
                        'name' => [
                            'familyName' => 'Alast',
                            'forename' => 'Bfirst',
                        ],
                        'action' => 'A',
                    ],
                    [
                        'name' => [
                            'familyName' => 'Alast',
                            'forename' => 'Cfirst',
                        ],
                        'action' => 'X',
                    ],
                ],
                'expect' => [
                    [
                        'name' => [
                            'familyName' => 'Alast',
                            'forename' => 'Cfirst',
                        ],
                        'action' => 'X',
                    ],
                    [
                        'name' => [
                            'familyName' => 'Alast',
                            'forename' => 'Bfirst',
                        ],
                        'action' => 'A',
                    ],
                    [
                        'name' => [
                            'familyName' => 'Xlast',
                            'forename' => 'Afirst',
                        ],
                        'action' => 'A',
                    ],
                ],
            ],
        ];
    }
}
