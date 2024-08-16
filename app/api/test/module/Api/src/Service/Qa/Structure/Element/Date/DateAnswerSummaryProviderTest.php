<?php

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

    public function testGetTemplateName()
    {
        $this->assertEquals(
            'generic',
            $this->dateAnswerSummaryProvider->getTemplateName()
        );
    }

    /**
     * @dataProvider dpSnapshot
     */
    public function testGetTemplateVariables($isSnapshot)
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

    public function dpSnapshot()
    {
        return [
            [true],
            [false]
        ];
    }
}
