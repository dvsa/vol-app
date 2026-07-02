<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Laminas\Form\Form;
use Laminas\Validator\ValidatorPluginManager;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Financial Evidence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationFinancialEvidence extends FinancialEvidence
{
    #[\Override]
    protected function alterForm($form): void
    {
        $this->removeFormAction($form, 'saveAndContinue');

        parent::alterForm($form);
    }
}
