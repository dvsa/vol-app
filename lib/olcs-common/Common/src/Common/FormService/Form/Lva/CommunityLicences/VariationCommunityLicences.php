<?php

namespace Common\FormService\Form\Lva\CommunityLicences;

use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Community Licences
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationCommunityLicences extends AbstractCommunityLicences
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService)
    {
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

        $this->removeStandardFormActions($form);

        return $form;
    }
}
