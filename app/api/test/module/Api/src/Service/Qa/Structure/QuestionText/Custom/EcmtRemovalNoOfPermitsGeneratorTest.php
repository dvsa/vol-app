<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\QuestionText\Custom;

use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\Custom\EcmtRemovalNoOfPermitsGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionText;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionTextGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * EcmtRemovalNoOfPermitsGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EcmtRemovalNoOfPermitsGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $feePerPermit = '18.00';

        $feeTypeEntity = m::mock(FeeTypeEntity::class);

        $translateableTextParameter = m::mock(TranslateableTextParameter::class);
        $translateableTextParameter->shouldReceive('setValue')
            ->with($feePerPermit)
            ->once();

        $questionText = m::mock(QuestionText::class);
        $questionText->shouldReceive('getGuidance->getTranslateableText->getParameter')
            ->with(0)
            ->andReturn($translateableTextParameter);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity->getFeePerPermit')
            ->with(null, $feeTypeEntity)
            ->andReturn($feePerPermit);

        $questionTextGenerator = m::mock(QuestionTextGenerator::class);
        $questionTextGenerator->shouldReceive('generate')
            ->with($qaContext)
            ->andReturn($questionText);

        $feeTypeRepo = m::mock(FeeTypeRepository::class);
        $feeTypeRepo->shouldReceive('getLatestByProductReference')
            ->with(FeeTypeEntity::FEE_TYPE_ECMT_REMOVAL_ISSUE_PRODUCT_REF)
            ->andReturn($feeTypeEntity);

        $ecmtRemovalNoOfPermitsGenerator = new EcmtRemovalNoOfPermitsGenerator(
            $questionTextGenerator,
            $feeTypeRepo
        );

        $this->assertSame(
            $questionText,
            $ecmtRemovalNoOfPermitsGenerator->generate($qaContext)
        );
    }
}
