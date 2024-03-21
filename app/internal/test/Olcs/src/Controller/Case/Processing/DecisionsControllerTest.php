<?php

/**
 * Case decisions controller tests
 */

namespace OlcsTest\Controller\Cases\Processing;

use Olcs\Controller\Cases\Processing\DecisionsController as Sut;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Case decisions controller tests
 */
class DecisionsControllerTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        parent::setUp();
    }

    /**
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction(
        $id,
        $data,
        $expectedRedirRouteName
    ) {
        $params = m::mock()
            ->shouldReceive('fromRoute')->with('case')->andReturn($id)
            ->getMock();

        $redirect = m::mock()
            ->shouldReceive('toRouteAjax')->with(
                $expectedRedirRouteName,
                m::type('array'),
                m::type('array'),
                true
            )
            ->once()
            ->andReturn('redirectResponse')
            ->getMock();

        $response = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn($data)
            ->getMock();

        $this->sut
            ->shouldReceive('params')
            ->andReturn($params)
            ->shouldReceive('handleQuery')
            ->once()
            ->andReturn($response)
            ->shouldReceive('redirect')
            ->once()
            ->andReturn($redirect);

        $this->assertEquals('redirectResponse', $this->sut->indexAction());
    }

    public function indexActionDataProvider()
    {
        return [
            // non-TM
            [
                100,
                [
                    'id' => 100,
                ],
                'processing_in_office_revocation',
            ],
            // TM
            [
                100,
                [
                    'id' => 100,
                    'transportManager' => [
                        'id' => 111,
                    ],
                ],
                'processing_decisions',
            ],
        ];
    }
}
