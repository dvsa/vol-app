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
final class BusDetailsControllerTest extends MockeryTestCase
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
        $data['localAuthoritys'] = [];
        $mockForm = $this->getAlterFormAssertions($data, $readonly);
        $this->expectLocalAuthoritiesExcluded($mockForm, [36, 98], [36, 98]);

        $result = $this->sut->alterFormForTa($mockForm, []);

        $this->assertSame($mockForm, $result);
    }

    /**
     * Authorities that have been replaced are not offered, except those the registration already
     * has: leaving one out would drop it from the registration when the form is saved
     */
    public function testAlterFormForTaKeepsReplacedAuthoritiesTheRegistrationHas(): void
    {
        $mockForm = $this->getAlterFormAssertions(
            ['isReadOnly' => false, 'localAuthoritys' => [['id' => 36], ['id' => 5]]],
            false
        );
        $this->expectLocalAuthoritiesExcluded($mockForm, [36, 62, 98], [62, 98]);

        $this->assertSame($mockForm, $this->sut->alterFormForTa($mockForm, []));
    }

    private function expectLocalAuthoritiesExcluded(m\MockInterface $mockForm, array $supersededIds, array $excluded): void
    {
        $dataService = m::mock(\Common\Service\Data\LocalAuthority::class);
        $dataService->expects('fetchSupersededIds')->withNoArgs()->andReturn($supersededIds);

        $localAuthorities = m::mock(\Common\Form\Element\DynamicSelect::class);
        $localAuthorities->expects('getDataService')->withNoArgs()->andReturn($dataService);
        $localAuthorities->expects('setExclude')->with($excluded)->andReturnSelf();

        $fields = m::mock(\Laminas\Form\Fieldset::class);
        $fields->expects('get')->with('localAuthoritys')->andReturn($localAuthorities);

        $mockForm->expects('get')->with('fields')->andReturn($fields);
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

    public static function alterFormProvider(): \Iterator
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
