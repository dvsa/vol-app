<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\PermitUsageGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionList;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionListFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio\Radio;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio\RadioFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PermitUsageGeneratorTest
 */
class PermitUsageGeneratorTest extends MockeryTestCase
{
    public $answerValue;
    public $options;
    public $applicationStepEntity;
    public $irhpPermitApplication;
    public $elementGeneratorContext;
    public $radioFactory;
    public $translateableTextGenerator;
    public $optionFactory;
    public $optionListFactory;
    public $sut;
    public function setUp(): void
    {
        $this->answerValue = RefData::JOURNEY_SINGLE;

        $this->options = [
            'notSelectedMessage' => ['key' => 'notSelectedMessageKey'],
        ];

        $this->applicationStepEntity = m::mock(ApplicationStepEntity::class);
        $this->applicationStepEntity->shouldReceive('getDecodedOptionSource')
            ->andReturn($this->options);

        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $this->elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $this->elementGeneratorContext->shouldReceive('getApplicationStepEntity')
            ->andReturn($this->applicationStepEntity);

        $this->elementGeneratorContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($this->irhpPermitApplication);

        $this->elementGeneratorContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($this->answerValue);

        $this->radioFactory = m::mock(RadioFactory::class);

        $this->translateableTextGenerator = m::mock(TranslateableTextGenerator::class);

        $this->optionFactory = m::mock(OptionFactory::class);

        $this->optionListFactory = m::mock(OptionListFactory::class);

        $this->sut = new PermitUsageGenerator(
            $this->radioFactory,
            $this->translateableTextGenerator,
            $this->optionFactory,
            $this->optionListFactory
        );

        parent::setUp();
    }

    public function testGenerate(): void
    {
        $returnedOptions = [
            (new RefData(RefData::JOURNEY_MULTIPLE))->setDescription('Multiple'),
            (new RefData(RefData::JOURNEY_SINGLE))->setDescription('Single'),
        ];

        $this->irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getPermitUsageList')
            ->withNoArgs()
            ->once()
            ->andReturn($returnedOptions);

        $optionList = m::mock(OptionList::class);
        $optionList->shouldReceive('add')
            ->with(RefData::JOURNEY_MULTIPLE, 'Multiple')
            ->once();
        $optionList->shouldReceive('add')
            ->with(RefData::JOURNEY_SINGLE, 'Single')
            ->once();

        $this->optionListFactory->shouldReceive('create')
            ->with($this->optionFactory)
            ->andReturn($optionList);

        $notSelectedMessageTranslateableText = m::mock(TranslateableText::class);

        $radio = m::mock(Radio::class);

        $this->radioFactory->shouldReceive('create')
            ->with($optionList, $notSelectedMessageTranslateableText, $this->answerValue)
            ->andReturn($radio);

        $this->translateableTextGenerator->shouldReceive('generate')
            ->with($this->options['notSelectedMessage'])
            ->andReturn($notSelectedMessageTranslateableText);

        $this->assertSame(
            $radio,
            $this->sut->generate($this->elementGeneratorContext)
        );
    }

    public function testGenerateWithEmptyPermitUsageList(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);
        $this->expectExceptionMessage('Permit usage not found');

        $returnedOptions = [];

        $this->irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getPermitUsageList')
            ->withNoArgs()
            ->once()
            ->andReturn($returnedOptions);

        $this->sut->generate($this->elementGeneratorContext);
    }
}
