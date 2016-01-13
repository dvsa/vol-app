<?php

/**
 * Application Business Type Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\BusinessType;

use Common\FormService\Form\Lva\BusinessType\ApplicationBusinessType as CommonApplicationBusinessType;
use Zend\Form\Form;

/**
 * Application Business Type Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBusinessType extends CommonApplicationBusinessType
{
    protected function alterForm(Form $form, $params)
    {
        parent::alterForm($form, $params);

        if ($params['inForceLicences']) {

            $this->removeFormAction($form, 'save');
            $this->removeFormAction($form, 'cancel');
            $this->addBackToOverviewLink($form, $this->lva, false);

            $this->lockForm($form, false);
        }
    }
}
