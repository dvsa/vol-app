<?php

/**
 * ConvictionsPenalties Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\ConvictionsPenalties as CommonConvictionsPenalties;

/**
 * ConvictionsPenalties Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ConvictionsPenalties extends CommonConvictionsPenalties
{
    /**
     * Make form alterations
     *
     * @param \Zend\Form\Form $form   form
     * @param array           $params params
     *
     * @return \Zend\Form\Form
     */
    protected function alterForm($form, array $params)
    {
        parent::alterForm($form, $params);

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        return $form;
    }
}
