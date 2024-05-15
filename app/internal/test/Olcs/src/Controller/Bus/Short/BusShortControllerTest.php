<?php

/**
 * Bus Short Controller Test
 */

namespace OlcsTest\Controller\Bus\Short;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Navigation\Navigation;
use Olcs\Controller\Bus\Short\BusShortController as Sut;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Bus Short Controller Test
 */
class BusShortControllerTest extends MockeryTestCase
{
    protected $sut;
    protected $translationHelper;
    protected $formHelper;
    protected $flashMessengerHelper;
    protected $navigation;

    public function setUp(): void
    {
        $this->translationHelper = m::mock(TranslationHelperService::class);
        $this->formHelper = m::mock(FormHelperService::class);
        $this->flashMessengerHelper =  m::mock(FlashMessengerHelperService::class);
        $this->navigation = m::mock(Navigation::class);

        $this->sut = new Sut($this->translationHelper, $this->formHelper, $this->flashMessengerHelper, $this->navigation);
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

        $mockForm = m::mock(\Laminas\Form\Form::class);
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
                ],
                true,
            ],
            [
                [
                    'isReadOnly' => false,
                ],
                false,
            ],
        ];
    }
}
