<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Application as CommonApplication;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Application extends CommonApplication
{
    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;

    public function __construct(FormHelperService $formHelper, AuthorizationService $authService)
    {
        parent::__construct($formHelper, $authService);
    }

    public function alterForm($form): void
    {
        parent::alterForm($form);

        if ($form->has('form-actions') && $form->get('form-actions')->has('save')) {
            $form->get('form-actions')->get('save')->setLabel('internal.save.button');
        }
    }
}
