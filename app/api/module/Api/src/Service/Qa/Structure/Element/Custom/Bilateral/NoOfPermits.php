<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;

class NoOfPermits implements ElementInterface
{
    /** @var array */
    private $texts = [];

    /**
     * Add an text element to the representation
     */
    public function addText(NoOfPermitsText $text)
    {
        $this->texts[] = $text;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepresentation()
    {
        $textRepresentations = [];
        foreach ($this->texts as $text) {
            $textRepresentations[] = $text->getRepresentation();
        }

        return ['texts' => $textRepresentations];
    }
}
