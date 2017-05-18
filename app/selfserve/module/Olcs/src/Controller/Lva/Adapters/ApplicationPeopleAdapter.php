<?php

namespace Olcs\Controller\Lva\Adapters;

/**
 * External Application People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ApplicationPeopleAdapter extends VariationPeopleAdapter
{
    /**
     * Can Modify
     *
     * @return bool
     */
    public function canModify()
    {
        if ($this->hasInforceLicences() === false) {
            return true;
        }

        return parent::canModify();
    }
}
