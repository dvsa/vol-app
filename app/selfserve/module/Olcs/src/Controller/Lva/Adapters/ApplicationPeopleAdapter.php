<?php

/**
 * External Application People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Adapters\AbstractAdapter;

/**
 * External Application People Adapter
 *
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ApplicationPeopleAdapter extends AbstractAdapter
{
    public function alterSoleTraderFormForOrganisation(Form $form, $orgId)
    {
        // if we haven't got any in force licences, crack on...
        if (!$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId)) {
            return;
        }

        return $this->getServiceLocator()->get('Lva\People')->lockSoleTrader($form);
    }
}
