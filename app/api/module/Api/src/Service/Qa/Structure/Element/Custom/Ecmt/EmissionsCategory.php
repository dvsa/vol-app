<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

class EmissionsCategory
{
    /**
     * Create instance
     *
     * @param string $type
     * @param int|null $value
     * @param int $permitsRemaining
     *
     * @return EmissionsCategory
     */
    public function __construct(private $type, private $value, private $permitsRemaining)
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
            'type' => $this->type,
            'value' => $this->value,
            'permitsRemaining' => $this->permitsRemaining,
        ];
    }
}
