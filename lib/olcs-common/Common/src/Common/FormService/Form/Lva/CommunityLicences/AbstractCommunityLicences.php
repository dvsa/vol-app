<?php

namespace Common\FormService\Form\Lva\CommunityLicences;

use Common\FormService\Form\Lva\AbstractLvaFormService;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Community Licences
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractCommunityLicences extends AbstractLvaFormService
{
    protected FormHelperService $formHelper;

    public function getForm()
    {
        $form = $this->formHelper->createForm('Lva\CommunityLicences');

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
