<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * Traffic Area Name (uppercase) bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class TaNameUppercase extends TaName
{
    #[\Override]
    public function render()
    {
        return strtoupper(parent::render());
    }
}
