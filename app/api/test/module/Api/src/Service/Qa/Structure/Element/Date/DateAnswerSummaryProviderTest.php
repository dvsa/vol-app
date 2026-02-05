<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Date;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Date\DateAnswerSummaryProvider;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * DateAnswerSummaryProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class DateAnswerSummaryProviderTest extends MockeryTestCase
{
    private $dateAnswerSummaryProvider;

    public function setUp(): void
    {
        $this->dateAnswerSummaryProvider = new DateAnswerSummaryProvider();
    }

    public function testGetTemplateName(): void
    {
        $this->assertEquals(
            'generic',
            $this->dateAnswerSummaryProvider->getTemplateName()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSnapshot')]
    public function testGetTemplateVariables(mixed $isSnapshot): void
    {
        $qaAnswer = '2020-05-02';

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($qaAnswer);

        $element = m::mock(ElementInterface::class);

        $templateVariables = $this->dateAnswerSummaryProvider->getTemplateVariables($qaContext, $element, $isSnapshot);

        $this->assertEquals(
            ['answer' => '02/05/2020'],
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
