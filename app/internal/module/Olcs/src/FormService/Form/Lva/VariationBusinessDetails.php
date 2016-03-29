<?php

/**
 * Variation Business Details Form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\BusinessDetails\VariationBusinessDetails as CommonVariationBusinessDetails;

/**
 * Variation Business Details Form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VariationBusinessDetails extends CommonVariationBusinessDetails
{
    public function alterForm($form, $params)
    {
        parent::alterForm($form, $params);
        $this->getFormHelper()->remove($form, 'allow-email');
    }
}
