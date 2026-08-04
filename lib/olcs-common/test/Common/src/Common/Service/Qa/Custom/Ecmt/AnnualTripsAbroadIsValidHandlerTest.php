<?php

declare(strict_types=1);

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Form\QaForm;
use Common\Service\Qa\Custom\Ecmt\AnnualTripsAbroadIsValidHandler;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * AnnualTripsAbroadIsValidHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class AnnualTripsAbroadIsValidHandlerTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValid')]
    public function testIsValid($permitsRequired, $intensityWarningThreshold, $warningVisibleValue, $expectedIsValid): void
    {
        $applicationStep = [
            'element' => [
                'intensityWarningThreshold' => $intensityWarningThreshold
            ]
        ];

        $questionFieldsetData = [
            'qaElement' => $permitsRequired,
            'warningVisible' => $warningVisibleValue
        ];

        $qaForm = m::mock(QaForm::class);
        $qaForm->shouldReceive('getApplicationStep')
            ->withNoArgs()
            ->andReturn($applicationStep);
        $qaForm->shouldReceive('getQuestionFieldsetData')
            ->withNoArgs()
            ->andReturn($questionFieldsetData);

        $annualTripsAbroadIsValidHandler = new AnnualTripsAbroadIsValidHandler();

        $this->assertEquals(
            $expectedIsValid,
            $annualTripsAbroadIsValidHandler->isValid($qaForm)
        );
    }

    /**
     * @return \Iterator<(int | string), array<(bool | int)>>
     *
     * @psalm-return list{list{4, 5, 1, true}, list{5, 5, 1, true}, list{6, 5, 1, true}, list{4, 5, 0, true}, list{5, 5, 0, true}, list{6, 5, 0, false}}
     */
    public static function dpIsValid(): \Iterator
    {
        yield [4, 5, 1, true];
        yield [5, 5, 1, true];
        yield [6, 5, 1, true];
        yield [4, 5, 0, true];
        yield [5, 5, 0, true];
        yield [6, 5, 0, false];
    }
}
