<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerSummaryProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * GenericAnswerSummaryProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class GenericAnswerSummaryProviderTest extends MockeryTestCase
{
    private $genericAnswerSummaryProvider;

    public function setUp(): void
    {
        $this->genericAnswerSummaryProvider = new GenericAnswerSummaryProvider();
    }

    public function testGetTemplateName(): void
    {
        $this->assertEquals(
            'generic',
            $this->genericAnswerSummaryProvider->getTemplateName()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSnapshot')]
    public function testGetTemplateVariables(mixed $isSnapshot): void
    {
        $answerValue = 'foo';

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($answerValue);

        $element = m::mock(ElementInterface::class);

        $templateVariables = $this->genericAnswerSummaryProvider->getTemplateVariables(
            $qaContext,
            $element,
            $isSnapshot
        );

        $this->assertEquals(
            ['answer' => $answerValue],
            $templateVariables
        );
    }

    public static function dpSnapshot(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
