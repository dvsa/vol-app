<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Safety as CommonSafety;
use Common\Service\Helper\FormHelperService;

/**
 * Safety Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Safety extends CommonSafety
{
    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        parent::__construct($formHelper);
    }

    /**
     * Make form alterations
     *
     * @return \Laminas\Form\Form
     */
    #[\Override]
    public function getForm()
    {
        $form = parent::getForm();

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        return $form;
    }
}
