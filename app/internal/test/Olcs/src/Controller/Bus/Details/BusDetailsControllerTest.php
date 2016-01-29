<?php

/**
 * Bus Details Controller Test
 */
namespace OlcsTest\Controller\Bus\Details;

use Olcs\Controller\Bus\Details\BusDetailsController as Sut;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Bus Details Controller Test
 */
class BusDetailsControllerTest extends MockeryTestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new Sut;
    }

    /**
     * @dataProvider alterFormProvider
     *
     * @param array $data
     * @param bool $readonly
     */
    public function testAlterFormForService($data, $readonly)
    {
        $mockForm = $this->getAlterFormAssertions($data, $readonly);
        $result = $this->sut->alterFormForService($mockForm, []);

        $this->assertSame($mockForm, $result);
    }

    /**
     * @dataProvider alterFormProvider
     *
     * @param array $data
     * @param bool $readonly
     */
    public function testAlterFormForTa($data, $readonly)
    {
        $mockForm = $this->getAlterFormAssertions($data, $readonly);
        $result = $this->sut->alterFormForTa($mockForm, []);

        $this->assertSame($mockForm, $result);
    }

    /**
     * @dataProvider alterFormProvider
     *
     * @param array $data
     * @param bool $readonly
     */
    public function testAlterFormForStop($data, $readonly)
    {
        $mockForm = $this->getAlterFormAssertions($data, $readonly);
        $result = $this->sut->alterFormForStop($mockForm, []);

        $this->assertSame($mockForm, $result);
    }

    /**
     * @dataProvider alterFormProvider
     *
     * @param array $data
     * @param bool $readonly
     */
    public function testAlterFormForQuality($data, $readonly)
    {
        $mockForm = $this->getAlterFormAssertions($data, $readonly);
        $result = $this->sut->alterFormForQuality($mockForm, []);

        $this->assertSame($mockForm, $result);
    }

    /**
     * @param array $data
     * @param bool $readonly
     * @return m\MockInterface
     */
    public function getAlterFormAssertions($data, $readonly)
    {
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

        return $mockForm;
    }

    public function alterFormProvider()
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
