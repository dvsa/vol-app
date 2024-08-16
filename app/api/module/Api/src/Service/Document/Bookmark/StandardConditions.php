<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * Standard Conditions bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class StandardConditions extends AbstractStandardConditions
{
    public function render()
    {
        $this->prefix = $this->data['niFlag'] === 'N' ? 'GB' : 'NI';

        return parent::render();
    }
}
