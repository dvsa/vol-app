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
class ApplicationBusinessDetailsAdapter extends AbstractAdapter implements BusinessDetailsAdapterInterface
{
    public function alterFormForOrganisation(Form $form, $orgId)
    {
        die("external app yo!");
    }
}
