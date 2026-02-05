<?php

namespace Olcs\FormService\Form\Lva\TransportManager;

use Common\FormService\Form\Lva\AbstractLvaFormService;
use Common\Service\Helper\FormHelperService;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;
use Common\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Transport Manager
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationTransportManager extends AbstractLvaFormService
{
    use ButtonsAlterations;

    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService)
    {
    }

    /**
     * Get form
     *
     * @return Form
     */
    public function getForm()
    {
        $form = $this->formHelper->createForm('Lva\TransportManagers');

        $this->alterForm($form);

        return $form;
    }

    /**
     * Alter form
     *
     * @param Form $form form
     *
     * @return Form
     */
    protected function alterForm($form)
    {
        $this->alterButtons($form);

        return $form;
    }
}
