<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Date;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;

class Date implements ElementInterface
{
    /**
     * Create instance
     *
     * @param string $value (optional)
     *
     * @return Date
     */
    public function __construct(private $value = null)
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
