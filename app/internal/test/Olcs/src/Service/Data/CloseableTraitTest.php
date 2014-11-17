<?php

namespace OlcsTest\Service\Data;

use Mockery as m;

/**
 * Class CloseableTraitTest
 * @package OlcsTest\Controller\Traits
 */
class CloseableTraitTest extends \PHPUnit_Framework_TestCase
{
    public $sut;

    public function setUp()
    {
        $this->sut = new \Olcs\Service\Data\Submission();
    }


    public function testCloseEntity()
    {
        $id = 99;
        $mockData = [
            'id' => $id,
            'version' => 1
        ];
        $mockRestClient = m::mock('Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->once()->withAnyArgs()->andReturn($mockData);
        $mockRestClient->shouldReceive('update')->once()->with(
            $mockData['id'],
            m::type('array')
        )->andReturn($mockData);

        $this->sut->setRestClient($mockRestClient);

        $this->assertNull($this->sut->closeEntity($id));
    }

    public function testReopenEntity()
    {
        $id = 99;
        $mockData = [
            'id' => $id,
            'version' => 1
        ];
        $mockRestClient = m::mock('Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->once()->withAnyArgs()->andReturn($mockData);
        $mockRestClient->shouldReceive('update')->once()->with(
            $mockData['id'],
            [
                'data' => json_encode(
                    [
                        'version' => $mockData['version'],
                        'closedDate' => null
                    ]
                )
            ]
        )->andReturnNull();

        $this->sut->setRestClient($mockRestClient);

        $this->assertNull($this->sut->reopenEntity($id));
    }


}
