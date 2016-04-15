<?php

namespace OlcsTest\Controller\Traits;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ListDataTraitTest
 * @package OlcsTest\Controller\Traits
 */
class ListDataTraitTest extends MockeryTestCase
{
    /**
     * @var \OlcsTest\Controller\Traits\Stub\StubListDataTrait
     */
    private $sut;

    public function setUp()
    {
        $this->sut = new \OlcsTest\Controller\Traits\Stub\StubListDataTrait();
    }

    public function testGetListDataEnforcementArea()
    {
        $response = m::mock(\Common\Service\Cqrs\Response::class);
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn(
            [
                'trafficAreaEnforcementAreas' => [
                    ['enforcementArea' => ['id' => 'V15', 'name' => 'Foo']],
                    ['enforcementArea' => ['id' => 'X16', 'name' => 'Bar']],
                ]
            ]
        );
        $this->sut->setHandleQueryResponse($response);

        $result = $this->sut->getListDataEnforcementArea('X', 'Option1');

        $this->assertSame(['' => 'Option1', 'V15' => 'Foo', 'X16' => 'Bar'], $result);
    }

    public function testGetListDataEnforcementAreaError()
    {
        $response = m::mock(\Common\Service\Cqrs\Response::class);
        $response->shouldReceive('isOk')->andReturn(false);
        $this->sut->setHandleQueryResponse($response);

        $result = $this->sut->getListDataEnforcementArea('X');

        $this->assertSame([], $result);
    }
}
