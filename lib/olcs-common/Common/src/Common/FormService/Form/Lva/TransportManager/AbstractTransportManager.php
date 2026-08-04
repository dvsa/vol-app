<?php

namespace Common\FormService\Form\Lva\TransportManager;

use Common\FormService\Form\Lva\AbstractLvaFormService;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Transport Manager Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractTransportManager extends AbstractLvaFormService
{
    protected FormHelperService $formHelper;

    public function getForm()
    {
        $form = $this->formHelper->createForm('Lva\TransportManagers');

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
        $this->removeFormAction($form, 'save');
        $this->removeFormAction($form, 'cancel');

        return $form;
    }
}
