<?php

/**
 * External Licence & Variation Business Type Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Interfaces\BusinessTypeAdapterInterface;
use Common\Controller\Lva\Adapters\AbstractAdapter;
use Common\Service\Data\CategoryDataService;

/**
 * External Licence & Variation Business Type Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceVariationBusinessTypeAdapter extends AbstractAdapter implements BusinessTypeAdapterInterface
{
    public function alterFormForOrganisation(Form $form, $orgId)
    {
        $this->getServiceLocator()->get('Lva\BusinessType')->lockType($form);
    }
}
