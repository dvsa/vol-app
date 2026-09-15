<?php

declare(strict_types=1);

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
final class BusShortControllerTest extends MockeryTestCase
{
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $translationHelper = m::mock(TranslationHelperService::class);
        $formHelper = m::mock(FormHelperService::class);
        $flashMessengerHelper =  m::mock(FlashMessengerHelperService::class);
        $navigation = m::mock(Navigation::class);

        $this->sut = new Sut($translationHelper, $formHelper, $flashMessengerHelper, $navigation);
    }

    /**
     *
     * @param $data
     * @param $readonly
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('alterFormForEditDataProvider')]
    public function testAlterFormForEdit(
        mixed $data,
        mixed $readonly
    ): void {
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
     * @return \Iterator<(int | string), mixed>
     */
    public static function alterFormForEditDataProvider(): \Iterator
    {
        yield [
            [
                'isReadOnly' => true,
            ],
            true,
        ];
        yield [
            [
                'isReadOnly' => false,
            ],
            false,
        ];
    }
}
