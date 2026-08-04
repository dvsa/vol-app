<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * PSV Vehicles Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PsvVehicles extends AbstractLvaFormService
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService)
    {
    }

    protected $showShareInfo = false;

    public function getForm()
    {
        $form = $this->formHelper->createForm('Lva\PsvVehicles');

        $this->alterForm($form);

        if ($this->showShareInfo === false) {
            $this->formHelper->remove($form, 'shareInfo');
        }

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
