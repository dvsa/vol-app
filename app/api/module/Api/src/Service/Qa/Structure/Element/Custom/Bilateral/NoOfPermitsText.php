<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

class NoOfPermitsText
{
    /**
     * Create instance
     *
     * @param string $name
     * @param string $label
     * @param string $hint
     * @param string|null $value
     *
     * @return NoOfPermitsText
     */
    public function __construct(private $name, private $label, private $hint, private $value)
    {
    }

    /**
     * Get the representation of this class to be returned by the API endpoint
     *
     * @return array
     */
    public function getRepresentation()
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'hint' => $this->hint,
            'value' => $this->value
        ];
    }
}
