<?php

/**
 * Application Business Type Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\BusinessType;

use Common\FormService\Form\Lva\BusinessType\ApplicationBusinessType as CommonApplicationBusinessType;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Application Business Type Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBusinessType extends CommonApplicationBusinessType
{
    use ServiceLocatorAwareTrait;

    protected function alterForm(Form $form, $params)
    {
        parent::alterForm($form, $params);

        if ($params['inForceLicences']) {
            $this->lockForm($form);
        }
    }
}
