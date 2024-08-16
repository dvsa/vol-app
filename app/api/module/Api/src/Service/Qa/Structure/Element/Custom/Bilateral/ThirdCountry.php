<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;

class ThirdCountry implements ElementInterface
{
    /**
     * Create instance
     *
     * @param string|null $yesNo
     *
     * @return ThirdCountry
     */
    public function __construct(private $yesNo)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepresentation()
    {
        return [
            'yesNo' => $this->yesNo,
        ];
    }
}
