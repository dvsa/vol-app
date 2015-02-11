<?php

/**
 * External Application Business Details Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Interfaces\BusinessDetailsAdapterInterface;
use Common\Controller\Lva\Adapters\AbstractAdapter;

/**
 * External Application Business Details Adapter
 *
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ApplicationBusinessDetailsAdapter extends LicenceVariationBusinessDetailsAdapter
{
    public function alterFormForOrganisation(Form $form, $orgId)
    {
        // if we haven't got any in force licences, crack on...
        if (!$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId)) {
            return;
        }

        return parent::alterFormForOrganisation($form, $orgId);
    }
}
