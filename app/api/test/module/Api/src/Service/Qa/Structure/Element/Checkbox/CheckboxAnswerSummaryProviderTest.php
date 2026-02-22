<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Checkbox;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Checkbox\Checkbox;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Checkbox\CheckboxAnswerSummaryProvider;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CheckboxAnswerSummaryProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CheckboxAnswerSummaryProviderTest extends MockeryTestCase
{
    private $checkboxAnswerSummaryProvider;

    public function setUp(): void
    {
        $this->checkboxAnswerSummaryProvider = new CheckboxAnswerSummaryProvider();
    }

    public function testGetTemplateName(): void
    {
        $this->assertEquals(
            'generic',
            $this->checkboxAnswerSummaryProvider->getTemplateName()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetTemplateVariables')]
    public function testGetTemplateVariables(mixed $isSnapshot): void
    {
        $labelKey = 'label.key';

        $representation = [
            Checkbox::LABEL_KEY => [
                'key' => $labelKey
            ]
        ];

        $qaContext = m::mock(QaContext::class);

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('getRepresentation')
            ->withNoArgs()
            ->andReturn($representation);

        $templateVariables = $this->checkboxAnswerSummaryProvider->getTemplateVariables($qaContext, $element, $isSnapshot);

        $this->assertEquals(
            ['answer' => $labelKey],
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
