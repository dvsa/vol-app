<?php

declare(strict_types=1);

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Form\QaForm;
use Common\Service\Qa\Custom\Bilateral\PermitUsageIsValidHandler;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PermitUsageIsValidHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class PermitUsageIsValidHandlerTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValid')]
    public function testIsValid($storedAnswerValue, $qaElementValue, $warningVisibleValue, $expectedIsValid): void
    {
        $questionFieldsetData = [
            'qaElement' => $qaElementValue,
            'warningVisible' => $warningVisibleValue
        ];

        $applicationStep = [
            'element' => [
                'value' => $storedAnswerValue
            ]
        ];

        $qaForm = m::mock(QaForm::class);
        $qaForm->shouldReceive('getQuestionFieldsetData')
            ->withNoArgs()
            ->andReturn($questionFieldsetData);
        $qaForm->shouldReceive('getApplicationStep')
            ->withNoArgs()
            ->andReturn($applicationStep);

        $permitUsageIsValidHandler = new PermitUsageIsValidHandler();

        $this->assertEquals(
            $expectedIsValid,
            $permitUsageIsValidHandler->isValid($qaForm)
        );
    }

    /**
     * @return \Iterator<(int | string), array<(bool | string | null)>>
     *
     * @psalm-return list{list{null, 'journey_multiple', false, true}, list{null, 'journey_multiple', true, true}, list{'journey_multiple', 'journey_multiple', false, true}, list{'journey_multiple', 'journey_multiple', true, true}, list{'journey_single', 'journey_multiple', true, true}, list{'journey_single', 'journey_multiple', false, false}}
     */
    public static function dpIsValid(): \Iterator
    {
        yield [null, 'journey_multiple', false, true];
        yield [null, 'journey_multiple', true, true];
        yield ['journey_multiple', 'journey_multiple', false, true];
        yield ['journey_multiple', 'journey_multiple', true, true];
        yield ['journey_single', 'journey_multiple', true, true];
        yield ['journey_single', 'journey_multiple', false, false];
    }
}
