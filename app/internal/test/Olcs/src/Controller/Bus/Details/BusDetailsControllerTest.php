<?php

declare(strict_types=1);

/**
 * Bus Details Controller Test
 */

namespace OlcsTest\Controller\Bus\Details;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Olcs\Controller\Bus\Details\BusDetailsController as Sut;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\Navigation\Navigation;

/**
 * Bus Details Controller Test
 */
class BusDetailsControllerTest extends MockeryTestCase
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
     *
     * @param array $data
     * @param bool  $readonly
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('alterFormProvider')]
    public function testAlterFormForService(mixed $data, mixed $readonly): void
    {
        $mockForm = $this->getAlterFormAssertions($data, $readonly);
        $result = $this->sut->alterFormForService($mockForm, []);

        $this->assertSame($mockForm, $result);
    }

    /**
     *
     * @param array $data
     * @param bool  $readonly
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('alterFormProvider')]
    public function testAlterFormForTa(mixed $data, mixed $readonly): void
    {
        $mockForm = $this->getAlterFormAssertions($data, $readonly);
        $result = $this->sut->alterFormForTa($mockForm, []);

        $this->assertSame($mockForm, $result);
    }

    /**
     *
     * @param array $data
     * @param bool  $readonly
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('alterFormProvider')]
    public function testAlterFormForStop(mixed $data, mixed $readonly): void
    {
        $mockForm = $this->getAlterFormAssertions($data, $readonly);
        $result = $this->sut->alterFormForStop($mockForm, []);

        $this->assertSame($mockForm, $result);
    }

    /**
     *
     * @param array $data
     * @param bool  $readonly
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('alterFormProvider')]
    public function testAlterFormForQuality(mixed $data, mixed $readonly): void
    {
        $mockForm = $this->getAlterFormAssertions($data, $readonly);
        $result = $this->sut->alterFormForQuality($mockForm, []);

        $this->assertSame($mockForm, $result);
    }

    /**
     * @param  array $data
     * @param  bool  $readonly
     * @return m\MockInterface
     */
    public function getAlterFormAssertions(mixed $data, mixed $readonly): m\MockInterface
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

        $mockForm = m::mock(\Laminas\Form\Form::class);
        $mockForm->shouldReceive('setOption')
            ->times($readonly ? 1 : 0)
            ->with('readonly', true);

        return $mockForm;
    }

    public static function alterFormProvider(): array
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
