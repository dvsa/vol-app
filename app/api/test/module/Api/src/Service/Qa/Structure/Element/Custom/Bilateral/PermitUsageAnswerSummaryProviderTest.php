<?php

declare(strict_types=1);

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
final class PermitUsageAnswerSummaryProviderTest extends MockeryTestCase
{
    private $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new PermitUsageAnswerSummaryProvider();
    }

    public function testGetTemplateName(): void
    {
        $this->assertEquals(
            'generic',
            $this->sut->getTemplateName()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpShouldIncludeSlug')]
    public function testShouldIncludeSlug(mixed $permitUsageList, mixed $expected): void
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

    public static function dpShouldIncludeSlug(): \Iterator
    {
        $emptyList = [];
        $oneRecord = [['id' => 1]];
        $multipleRecords = [['id' => 1], ['id' => 2]];
        yield [$emptyList, false];
        yield [$oneRecord, false];
        yield [$multipleRecords, true];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetTemplateVariables')]
    public function testGetTemplateVariables(mixed $isSnapshot): void
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

    public static function dpGetTemplateVariables(): \Iterator
    {
        yield [true];
        yield [false];
    }
}
