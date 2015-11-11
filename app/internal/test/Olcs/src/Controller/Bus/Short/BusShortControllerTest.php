<?php

/**
 * Bus Short Controller Test
 */
namespace OlcsTest\Controller\Bus\Short;

use Olcs\Controller\Bus\Short\BusShortController as Sut;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Bus Short Controller Test
 */
class BusShortControllerTest extends MockeryTestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new Sut;
    }

    /**
     * @dataProvider alterFormForEditDataProvider
     *
     * @param $data
     * @param $readonly
     */
    public function testAlterFormForEdit(
        $data,
        $readonly
    ) {
        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $response = m::mock();
        $response->shouldReceive('getResult')
            ->once()
            ->andReturn($data);

        $busRegId = 56;
        $params = m::mock()
            ->shouldReceive('fromRoute')->with('busRegId')->andReturn($busRegId)
            ->getMock();

        $this->sut
            ->shouldReceive('handleQuery')
            ->once()
            ->andReturn($response);

        $this->sut
            ->shouldReceive('params')
            ->once()
            ->andReturn($params);

        $mockForm = m::mock('\Zend\Form\Form');
        $mockForm->shouldReceive('setOption')
            ->times($readonly ? 1 : 0)
            ->with('readonly', true);

        $result = $this->sut->alterFormForEdit($mockForm, []);

        $this->assertSame($mockForm, $result);
    }

    /**
     * @return array
     */
    public function alterFormForEditDataProvider()
    {
        return [
            [
                [
                    'isReadOnly' => true,
                    'isFromEbsr' => true,
                ],
                true,
            ],
            [
                [
                    'isReadOnly' => true,
                    'isFromEbsr' => false,
                ],
                true,
            ],
            [
                [
                    'isReadOnly' => false,
                    'isFromEbsr' => true,
                ],
                true,
            ],
            [
                [
                    'isReadOnly' => false,
                    'isFromEbsr' => false,
                ],
                false,
            ],
        ];
    }
}
