<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\QuestionText\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\Custom\Bilateral\CabotageGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionText;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionTextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CabotageGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CabotageGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $countryCode = 'DE';

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getCountry->getId')
            ->withNoArgs()
            ->andReturn($countryCode);

        $additionalGuidanceTranslateableText = m::mock(TranslateableText::class);
        $additionalGuidanceTranslateableText->shouldReceive('getKey')
            ->withNoArgs()
            ->andReturn('key.containing.sprintf.placeholder.%s');
        $additionalGuidanceTranslateableText->shouldReceive('setKey')
            ->with('key.containing.sprintf.placeholder.de')
            ->once();

        $questionText = m::mock(QuestionText::class);
        $questionText->shouldReceive('getAdditionalGuidance->getTranslateableText')
            ->withNoArgs()
            ->andReturn($additionalGuidanceTranslateableText);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);

        $questionTextGenerator = m::mock(QuestionTextGenerator::class);
        $questionTextGenerator->shouldReceive('generate')
            ->with($qaContext)
            ->andReturn($questionText);

        $cabotageGenerator = new CabotageGenerator($questionTextGenerator);

        $this->assertSame(
            $questionText,
            $cabotageGenerator->generate($qaContext)
        );
    }
}
