<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Variation as CommonVariation;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Variation extends CommonVariation
{
    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;

    public function __construct(FormHelperService $formHelper, AuthorizationService $authService)
    {
        parent::__construct($formHelper, $authService);
    }

    public function alterForm($form)
    {
        parent::alterForm($form);

        if ($form->has('form-actions') && $form->get('form-actions')->has('save')) {
            $form->get('form-actions')->get('save')->setLabel('internal.save.button');
        }
    }
}
