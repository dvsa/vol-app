<?php

/**
 * External Application Business Type Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Interfaces\BusinessTypeAdapterInterface;
use Common\Controller\Lva\Adapters\AbstractAdapter;

/**
 * External Application Business Type Adapter
 *
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ApplicationBusinessTypeAdapter extends AbstractAdapter implements BusinessTypeAdapterInterface
{
    public function alterFormForOrganisation(Form $form, $orgId)
    {
        // if we haven't got any in force licences, crack on...
        if (!$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId)) {
            return;
        }

        $this->getServiceLocator()->get('Lva\BusinessType')->lockType($form);
    }
}
