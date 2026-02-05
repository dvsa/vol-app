<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsMoroccoAnswerSummaryProvider;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsMoroccoAnswerSummaryProviderTest
 */
class NoOfPermitsMoroccoAnswerSummaryProviderTest extends MockeryTestCase
{
    private $sut;

    public function setUp(): void
    {
        $this->sut = new NoOfPermitsMoroccoAnswerSummaryProvider();
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
        $permitsRequired = 45;

        $bilateralRequired = [
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => $permitsRequired
        ];

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getFilteredBilateralRequired')
            ->withNoArgs()
            ->andReturn($bilateralRequired);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);

        $element = m::mock(ElementInterface::class);

        $templateVariables = $this->sut->getTemplateVariables($qaContext, $element, $isSnapshot);

        $this->assertEquals(
            ['answer' => $permitsRequired],
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
