<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\StandardAndCabotageAnswerSummaryProvider;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StandardAndCabotageAnswerSummaryProviderTest
 */
final class StandardAndCabotageAnswerSummaryProviderTest extends MockeryTestCase
{
    private $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new StandardAndCabotageAnswerSummaryProvider();
    }

    public function testGetTemplateName(): void
    {
        $this->assertEquals(
            'bilateral-standard-and-cabotage',
            $this->sut->getTemplateName()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetTemplateVariables')]
    public function testGetTemplateVariables(mixed $cabotageSelection, mixed $isSnapshot, mixed $expectedTemplateVariables): void
    {
        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity->getBilateralCabotageSelection')
            ->withNoArgs()
            ->andReturn($cabotageSelection);

        $element = m::mock(ElementInterface::class);

        $this->assertEquals(
            $expectedTemplateVariables,
            $this->sut->getTemplateVariables($qaContext, $element, $isSnapshot)
        );
    }

    public static function dpGetTemplateVariables(): \Iterator
    {
        $expectedCabotageOnlyTemplateVariables = [
            'yesNo' => 'qanda.bilaterals.cabotage.yes-answer',
            'additionalInfo' => Answer::BILATERAL_CABOTAGE_ONLY,
        ];

        $expectedStandardAndCabotageTemplateVariables = [
            'yesNo' => 'qanda.bilaterals.cabotage.yes-answer',
            'additionalInfo' => Answer::BILATERAL_STANDARD_AND_CABOTAGE,
        ];

        $expectedStandardOnlyTemplateVariables = [
            'yesNo' => 'qanda.bilaterals.cabotage.no-answer',
            'additionalInfo' => null,
        ];
        yield [Answer::BILATERAL_CABOTAGE_ONLY, true, $expectedCabotageOnlyTemplateVariables];
        yield [Answer::BILATERAL_STANDARD_AND_CABOTAGE, true, $expectedStandardAndCabotageTemplateVariables];
        yield [Answer::BILATERAL_STANDARD_ONLY, true, $expectedStandardOnlyTemplateVariables];
        yield [Answer::BILATERAL_CABOTAGE_ONLY, false, $expectedCabotageOnlyTemplateVariables];
        yield [Answer::BILATERAL_STANDARD_AND_CABOTAGE, false, $expectedStandardAndCabotageTemplateVariables];
        yield [Answer::BILATERAL_STANDARD_ONLY, false, $expectedStandardOnlyTemplateVariables];
    }
}
