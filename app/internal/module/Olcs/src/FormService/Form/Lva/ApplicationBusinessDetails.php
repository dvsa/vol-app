<?php

/**
 * Application Business Details Form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\BusinessDetails\ApplicationBusinessDetails as CommonApplicationBusinessDetails;

/**
 * Application Business Details Form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationBusinessDetails extends CommonApplicationBusinessDetails
{
    public function alterForm($form, $params)
    {
        parent::alterForm($form, $params);
        $this->getFormHelper()->remove($form, 'allow-email');
    }
}
