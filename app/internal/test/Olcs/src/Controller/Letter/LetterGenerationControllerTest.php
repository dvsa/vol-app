<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Letter;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Navigation\Navigation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Letter\LetterGenerationController as Sut;

/**
 * Covers the radio "pick one" letter-choice validation (VOL-7282/VOL-7303).
 */
final class LetterGenerationControllerTest extends MockeryTestCase
{
    private function makeSut(array $letterChoices): Sut
    {
        $sut = m::mock(Sut::class, [
            m::mock(TranslationHelperService::class),
            m::mock(FormHelperService::class),
            m::mock(FlashMessengerHelperService::class),
            m::mock(Navigation::class),
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('fetchLetterChoicesForLetterType')->andReturn($letterChoices);

        return $sut;
    }

    private function validate(Sut $sut, array $selectedChoices): ?string
    {
        $method = new \ReflectionMethod(Sut::class, 'validateRequiredRadioChoices');

        return $method->invoke($sut, 1, $selectedChoices);
    }

    public function testCheckboxOnlyChoicesAreNeverRejected(): void
    {
        $sut = $this->makeSut([
            ['id' => 10, 'label' => 'Is Final', 'groupLabel' => 'Other', 'inputType' => 'checkbox'],
        ]);

        $this->assertNull($this->validate($sut, []));
        $this->assertNull($this->validate($sut, ['10']));
    }

    public function testRadioGroupWithExactlyOneSelectionPasses(): void
    {
        $sut = $this->makeSut([
            ['id' => 1, 'label' => 'England', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
            ['id' => 2, 'label' => 'Scotland', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
            ['id' => 3, 'label' => 'Wales', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
        ]);

        $this->assertNull($this->validate($sut, ['2']));
    }

    public function testRadioGroupWithNoSelectionIsRejected(): void
    {
        $sut = $this->makeSut([
            ['id' => 1, 'label' => 'England', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
            ['id' => 2, 'label' => 'Scotland', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
        ]);

        $error = $this->validate($sut, []);
        $this->assertNotNull($error);
        $this->assertStringContainsString('Nation', $error);
    }

    public function testRadioGroupWithMultipleSelectionsIsRejected(): void
    {
        $sut = $this->makeSut([
            ['id' => 1, 'label' => 'England', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
            ['id' => 2, 'label' => 'Scotland', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
        ]);

        $this->assertNotNull($this->validate($sut, ['1', '2']));
    }

    public function testMultipleRadioGroupsEachNeedExactlyOne(): void
    {
        $sut = $this->makeSut([
            ['id' => 1, 'label' => 'England', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
            ['id' => 2, 'label' => 'Scotland', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
            ['id' => 5, 'label' => 'First', 'groupLabel' => 'Stage', 'inputType' => 'radio'],
            ['id' => 6, 'label' => 'Final', 'groupLabel' => 'Stage', 'inputType' => 'radio'],
            ['id' => 9, 'label' => 'Is urgent', 'groupLabel' => 'Other', 'inputType' => 'checkbox'],
        ]);

        // Both groups satisfied (checkbox ignored)
        $this->assertNull($this->validate($sut, ['1', '6', '9']));
        // Second group ("Stage") unsatisfied
        $error = $this->validate($sut, ['1']);
        $this->assertNotNull($error);
        $this->assertStringContainsString('Stage', $error);
    }

    public function testFetchLetterChoicesSortsByDisplayOrder(): void
    {
        // VOL-7282: admin sets First request = 1, Final request = 2, but the modal
        // showed them in insertion order.
        $sut = m::mock(Sut::class, [
            m::mock(TranslationHelperService::class),
            m::mock(FormHelperService::class),
            m::mock(FlashMessengerHelperService::class),
            m::mock(Navigation::class),
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('fetchTemplateById')->with(1)->andReturn(['letterType' => ['id' => 7]]);

        $response = m::mock();
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn([
            'letterTypeChoices' => [
                ['letterChoice' => ['id' => 20, 'label' => 'Final request', 'groupLabel' => 'First or final request', 'inputType' => 'radio', 'displayOrder' => 2, 'isActive' => true]],
                ['letterChoice' => ['id' => 10, 'label' => 'First request', 'groupLabel' => 'First or final request', 'inputType' => 'radio', 'displayOrder' => 1, 'isActive' => true]],
            ],
        ]);
        $sut->shouldReceive('handleQuery')->andReturn($response);

        $method = new \ReflectionMethod(Sut::class, 'fetchLetterChoicesForLetterType');
        $method->setAccessible(true);
        $choices = $method->invoke($sut, 1);

        $this->assertSame(['First request', 'Final request'], array_column($choices, 'label'));
    }
}
