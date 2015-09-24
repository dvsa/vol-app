<?php

/**
 * Application Transport Manager
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\TransportManager;

use Common\FormService\Form\Lva\TransportManager\ApplicationTransportManager as CommonApplicationTransportManager;

/**
 * Application Transport Manager
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationTransportManager extends CommonApplicationTransportManager
{
    protected function alterForm($form)
    {
        $form = parent::alterForm($form);

        $this->addBackToOverviewLink($form, 'application', false);

        return $form;
    }
}
