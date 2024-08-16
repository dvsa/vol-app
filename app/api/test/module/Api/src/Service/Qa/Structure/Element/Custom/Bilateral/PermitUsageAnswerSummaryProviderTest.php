<?php

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\PermitUsageAnswerSummaryProvider;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PermitUsageAnswerSummaryProviderTest
 */
class PermitUsageAnswerSummaryProviderTest extends MockeryTestCase
{
    private $sut;

    public function setUp(): void
    {
        $this->sut = new PermitUsageAnswerSummaryProvider();
    }

    public function testGetTemplateName()
    {
        $this->assertEquals(
            'generic',
            $this->sut->getTemplateName()
        );
    }

    /**
     * @dataProvider dpShouldIncludeSlug
     */
    public function testShouldIncludeSlug($permitUsageList, $expected)
    {
        $qaEntity = m::mock(QaEntityInterface::class);
        $qaEntity->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getPermitUsageList')
            ->withNoArgs()
            ->andReturn($permitUsageList);

        $this->assertSame(
            $expected,
            $this->sut->shouldIncludeSlug($qaEntity)
        );
    }

    public function dpShouldIncludeSlug()
    {
        $emptyList = [];
        $oneRecord = [['id' => 1]];
        $multipleRecords = [['id' => 1], ['id' => 2]];

        return [
            [$emptyList, false],
            [$oneRecord, false],
            [$multipleRecords, true],
        ];
    }

    /**
     * @dataProvider dpGetTemplateVariables
     */
    public function testGetTemplateVariables($isSnapshot)
    {
        $answer = 'answer';

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($answer);

        $element = m::mock(ElementInterface::class);

        $templateVariables = $this->sut->getTemplateVariables($qaContext, $element, $isSnapshot);

        $this->assertEquals(
            ['answer' => $answer],
            $templateVariables
        );
    }

    public function dpGetTemplateVariables()
    {
        return [
            [true],
            [false],
        ];
    }
}
