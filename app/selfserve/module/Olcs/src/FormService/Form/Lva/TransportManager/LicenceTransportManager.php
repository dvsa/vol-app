<?php

/**
 * Licence Transport Manager
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\TransportManager;

use Common\FormService\Form\Lva\TransportManager\LicenceTransportManager as CommonLicenceTransportManager;

/**
 * Licence Transport Manager
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceTransportManager extends CommonLicenceTransportManager
{
    protected function alterForm($form)
    {
        $form = parent::alterForm($form);

        $this->addBackToOverviewLink($form, 'licence');

        return $form;
    }
}
