<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Supports\AnyTrait;

class TextGenerator implements ElementGeneratorInterface
{
    use AnyTrait;

    /**
     * Create service instance
     *
     *
     * @return Text
     */
    public function __construct(private TextFactory $textFactory, private TranslateableTextGenerator $translateableTextGenerator)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ElementGeneratorContext $context)
    {
        $applicationStepEntity = $context->getApplicationStepEntity();
        $options = $applicationStepEntity->getDecodedOptionSource();

        $label = null;
        if (isset($options['label'])) {
            $label = $this->translateableTextGenerator->generate($options['label']);
        }

        $hint = null;
        if (isset($options['hint'])) {
            $hint = $this->translateableTextGenerator->generate($options['hint']);
        }

        return $this->textFactory->create(
            $context->getAnswerValue(),
            $label,
            $hint
        );
    }
}
