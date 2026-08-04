<?php

namespace Common\FormService\Form\Lva\CommunityLicences;

use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Community Licences
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationCommunityLicences extends AbstractCommunityLicences
{
    public function __construct(FormHelperService $formHelper, protected AuthorizationService $authService)
    {
        $this->formHelper = $formHelper;
    }

    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form
     * @return \Laminas\Form\Form
     */
    #[\Override]
    protected function alterForm($form)
    {
        parent::alterForm($form);

        $this->removeFormAction($form, 'save');
        $this->removeFormAction($form, 'cancel');

        return $form;
    }
}
