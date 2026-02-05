<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\CabotageOnlyAnswerSummaryProvider;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CabotageOnlyAnswerSummaryProviderTest
 */
class CabotageOnlyAnswerSummaryProviderTest extends MockeryTestCase
{
    private $sut;

    public function setUp(): void
    {
        $this->sut = new CabotageOnlyAnswerSummaryProvider();
    }

    public function testGetTemplateName(): void
    {
        $this->assertEquals(
            'generic',
            $this->sut->getTemplateName()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetTemplateVariables')]
    public function testGetTemplateVariables(mixed $isSnapshot): void
    {
        $qaContext = m::mock(QaContext::class);
        $element = m::mock(ElementInterface::class);

        $templateVariables = $this->sut->getTemplateVariables($qaContext, $element, $isSnapshot);

        $this->assertEquals(
            ['answer' => 'qanda.bilaterals.cabotage.yes-answer'],
            $templateVariables
        );
    }

    public static function dpGetTemplateVariables(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
