<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;

class StandardAndCabotage implements ElementInterface
{
    /**
     * Create instance
     *
     * @param string|null $value
     *
     * @return StandardAndCabotage
     */
    public function __construct(private $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepresentation()
    {
        return [
            'value' => $this->value
        ];
    }
}
