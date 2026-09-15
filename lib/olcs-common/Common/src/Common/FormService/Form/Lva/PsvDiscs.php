<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * PSV Discs Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PsvDiscs extends AbstractLvaFormService
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService)
    {
    }

    public function getForm()
    {
        $form = $this->formHelper->createForm('Lva\PsvDiscs');

        $this->alterForm($form);

        return $form;
    }

    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form
     * @return \Laminas\Form\Form
     */
    protected function alterForm($form)
    {
        return $form;
    }
}
