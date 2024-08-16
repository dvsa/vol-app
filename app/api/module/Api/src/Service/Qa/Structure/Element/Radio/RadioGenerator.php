<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionListGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Supports\AnyTrait;

class RadioGenerator implements ElementGeneratorInterface
{
    use AnyTrait;

    /**
     * Create service instance
     *
     *
     * @return RadioGenerator
     */
    public function __construct(private RadioFactory $radioFactory, private OptionListGenerator $optionListGenerator, private TranslateableTextGenerator $translateableTextGenerator)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ElementGeneratorContext $context)
    {
        $applicationStepEntity = $context->getApplicationStepEntity();
        $options = $applicationStepEntity->getDecodedOptionSource();

        return $this->radioFactory->create(
            $this->optionListGenerator->generate($options['source']),
            $this->translateableTextGenerator->generate($options['notSelectedMessage']),
            $context->getAnswerValue()
        );
    }
}
