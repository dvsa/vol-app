<?php

/**
 * TaxiPhv Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\TaxiPhv as CommonTaxiPhv;

/**
 * TaxiPhv Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TaxiPhv extends CommonTaxiPhv
{
    /**
     * Make form alterations
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    protected function alterForm($form)
    {
        parent::alterForm($form);

        return $form;
    }
}
