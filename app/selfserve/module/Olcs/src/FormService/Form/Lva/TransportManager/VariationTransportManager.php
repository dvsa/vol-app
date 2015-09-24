<?php

/**
 * Variation Transport Manager
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\TransportManager;

use Common\FormService\Form\Lva\TransportManager\VariationTransportManager as CommonVariationTransportManager;

/**
 * Variation Transport Manager
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationTransportManager extends CommonVariationTransportManager
{
    protected function alterForm($form)
    {
        $form = parent::alterForm($form);

        $this->addBackToOverviewLink($form, 'variation');

        return $form;
    }
}
