<?php

/**
 * Variation Business Type Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\BusinessType;

use Common\FormService\Form\Lva\BusinessType\VariationBusinessType as CommonVariationBusinessType;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Variation Business Type Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationBusinessType extends CommonVariationBusinessType
{
    public function alterForm(Form $form, $params)
    {
        parent::alterForm($form, $params);

        $this->lockForm($form);
    }
}
