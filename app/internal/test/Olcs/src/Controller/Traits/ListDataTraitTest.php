<?php

namespace OlcsTest\Controller\Traits;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Olcs\Controller\Traits\ListDataTrait
 */
class ListDataTraitTest extends MockeryTestCase
{
    /**
     * @var \OlcsTest\Controller\Traits\Stub\StubListDataTrait
     */
    private $sut;
    /** @var  m\MockInterface */
    private $mockResponse;

    public function setUp(): void
    {
        $this->mockResponse = m::mock(\Common\Service\Cqrs\Response::class);

        $this->sut = new \OlcsTest\Controller\Traits\Stub\StubListDataTrait();
        $this->sut->setHandleQueryResponse($this->mockResponse);
    }

    public function testGetListDataEnforcementArea()
    {
        $this->mockResponse->shouldReceive('isOk')->andReturn(true);
        $this->mockResponse->shouldReceive('getResult')->andReturn(
            [
                'trafficAreaEnforcementAreas' => [
                    ['enforcementArea' => ['id' => 'V15', 'name' => 'Foo']],
                    ['enforcementArea' => ['id' => 'X16', 'name' => 'Bar']],
                ]
            ]
        );

        $result = $this->sut->getListDataEnforcementArea('X', 'Option1');

        $this->assertSame(['' => 'Option1', 'V15' => 'Foo', 'X16' => 'Bar'], $result);
    }

    public function testGetListDataEnforcementAreaError()
    {
        $this->mockResponse->shouldReceive('isOk')->andReturn(false);

        $result = $this->sut->getListDataEnforcementArea('X');

        $this->assertSame([], $result);
    }

    public function testGetListDataOptionsApiErr()
    {
        $this->mockResponse->shouldReceive('isOk')->andReturn(false);

        static::assertEquals([], $this->sut->getListDataUser());
    }

    public function testGetListDataOptions()
    {
        $respResult = [
            'results' => [
                [
                    'id' => 'unit_Id1',
                    'loginId' => 'unit_Val1',
                ],
            ],
        ];

        $this->mockResponse
            ->shouldReceive('isOk')->once()->andReturn(true)
            ->shouldReceive('getResult')->once()->andReturn($respResult);

        $actual = $this->sut->getListDataUser(null, ['unit_Key' => 'unit_Val']);

        static::assertEquals(
            [
                'unit_Key' => 'unit_Val',
                'unit_Id1' => 'unit_Val1',
            ],
            $actual
        );
    }
}
