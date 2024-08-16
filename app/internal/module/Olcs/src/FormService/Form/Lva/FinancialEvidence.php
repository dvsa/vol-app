<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\FinancialEvidence as CommonFinancialEvidence;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Laminas\Validator\ValidatorPluginManager;
use Olcs\FormService\Form\Lva\Traits\FinancialEvidenceAlterations;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * FinancialEvidence Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialEvidence extends CommonFinancialEvidence
{
    use FinancialEvidenceAlterations;

    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;
    protected UrlHelperService $urlHelper;
    protected TranslationHelperService $translator;

    public function __construct(
        FormHelperService $formHelper,
        AuthorizationService $authService,
        TranslationHelperService $translator,
        UrlHelperService $urlHelper,
        ValidatorPluginManager $validatorPluginManager
    ) {
        parent::__construct($formHelper, $authService, $translator, $urlHelper, $validatorPluginManager);
    }
}
