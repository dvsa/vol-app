<?php

namespace OlcsTest\Controller\Traits;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * @covers \Olcs\Controller\Traits\TaskSearchTrait
 */
class TaskSearchTraitTest extends MockeryTestCase
{
    const ID = 99999;
    const TEAM_ID = 8888;

    /** @var \OlcsTest\Controller\Traits\Stub\StubTaskSearchTrait */
    private $sut;

    public function setUp()
    {
        $this->sut = new \OlcsTest\Controller\Traits\Stub\StubTaskSearchTrait();
    }

    public function testUpdateSelectValueOptions()
    {
        $options = [
            'exists1' => 'exists1_Val',
            'exists2' => 'exists2_Val',
            'exists3' => 'exists3_Val',
        ];

        $changeOptions =
            [
                'exists2' => 'exists2_NewVal',
                'new1' => 'new Val',
                'exists1' => null,
                'new2' => '',
                'new3' => null,
            ];

        $mockEl = m::mock(\Zend\Form\Element\Select::class);
        $mockEl->shouldReceive('getValueOptions')->once()->andReturn($options);
        $mockEl->shouldReceive('setValueOptions')
            ->once()
            ->andReturnUsing(
                function ($arg) {
                    static::assertEquals(
                        [
                            'exists2' => 'exists2_NewVal',
                            'exists3' => 'exists3_Val',
                            'new1' => 'new Val',
                            'new2' => '',
                        ],
                        $arg
                    );

                    return $this;
                }
            );

        $this->sut->traitUpdateSelectValueOptions($mockEl, $changeOptions);
    }

    /**
     * @dataProvider dpTestMapTaskFilters
     */
    public function testMapTaskFilters($extra, $expect)
    {
        $userData = [
            'id' => self::ID,
            'team' => [
                'id' => self::TEAM_ID,
            ],
        ];

        $this->sut->currentUser = m::mock(\Zend\Http\Request::class)
            ->shouldReceive('getUserData')->once()->andReturn($userData)
            ->getMock();

        $mockRequest = m::mock(\Zend\Http\Request::class);
        $mockRequest->shouldReceive('getQuery->toArray')->once()->andReturn(['query' => 'unit_Query']);

        $this->sut->request = $mockRequest;

        static::assertEquals(
            $expect,
            $this->sut->traitMapTaskFilters($extra)
        );
    }

    public function dpTestMapTaskFilters()
    {
        return [
            [
                'extra' => [
                    'assignedToUser' => null,
                    'assignedToTeam' => null,
                    'date' => 'unit_Date',
                    'status' => 'unit_Status',
                    'sort' => null,
                    'order' => '',
                    'page' => 0,
                    'showTasks' => '0',
                    'newKey' => 'unit_NewKey',
                ],
                'expect' =>
                    [
                        'date' => 'unit_Date',
                        'status' => 'unit_Status',
                        'limit' => 10,
                        'newKey' => 'unit_NewKey',
                        'query' => 'unit_Query',
                    ],
            ],
        ];
    }
}
