<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * Licence holder correspondence address bookmark
 */
class LicCorrespondenceAddress extends LicenceHolderAddress
{
    #[\Override]
    public function render()
    {
        if (isset($this->data['correspondenceCd']['address'])) {
            $addressFormatter = new Formatter\Address();
            $addressFormatter->setSeparator(', ');
            return $addressFormatter->format($this->data['correspondenceCd']['address']);
        }
        return '';
    }
}
