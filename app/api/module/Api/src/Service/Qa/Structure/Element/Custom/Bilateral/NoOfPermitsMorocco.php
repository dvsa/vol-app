<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;

class NoOfPermitsMorocco implements ElementInterface
{
    /**
     * Create instance
     *
     * @param string $label
     * @param string|null $value
     *
     * @return NoOfPermitsMorocco
     */
    public function __construct(private $label, private $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepresentation()
    {
        return [
            'label' => $this->label,
            'value' => $this->value
        ];
    }
}
