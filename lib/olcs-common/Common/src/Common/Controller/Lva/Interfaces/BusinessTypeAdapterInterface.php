<?php

/**
 * Business Type Adapter Interface
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Common\Controller\Lva\Interfaces;

use Laminas\Form\Form;

/**
 * Business Type Adapter Interface
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
interface BusinessTypeAdapterInterface
{
    public function alterFormForOrganisation(Form $form, $orgId);
}
