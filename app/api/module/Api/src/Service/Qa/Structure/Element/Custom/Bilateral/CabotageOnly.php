<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;

class CabotageOnly implements ElementInterface
{
    /**
     * Create instance
     *
     * @param string|null $yesNo
     * @param string $countryName
     *
     * @return CabotageOnly
     */
    public function __construct(private $yesNo, private $countryName)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepresentation()
    {
        return [
            'yesNo' => $this->yesNo,
            'countryName' => $this->countryName,
        ];
    }
}
