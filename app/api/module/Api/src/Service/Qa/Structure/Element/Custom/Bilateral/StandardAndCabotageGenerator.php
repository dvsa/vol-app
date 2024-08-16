<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\AnyTrait;

class StandardAndCabotageGenerator implements ElementGeneratorInterface
{
    use AnyTrait;

    /**
     * Create service instance
     *
     *
     * @return StandardAndCabotageGenerator
     */
    public function __construct(private StandardAndCabotageFactory $standardAndCabotageFactory)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ElementGeneratorContext $context)
    {
        return $this->standardAndCabotageFactory->create(
            $context->getAnswerValue()
        );
    }
}
