<?php

/**
 * Application Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Application as CommonApplication;

/**
 * Application Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Application extends CommonApplication
{
    public function alterForm($form)
    {
        parent::alterForm($form);

        if ($form->has('form-actions') && $form->get('form-actions')->has('save')) {
            $form->get('form-actions')->get('save')->setLabel('internal.save.button');
        }
    }
}
