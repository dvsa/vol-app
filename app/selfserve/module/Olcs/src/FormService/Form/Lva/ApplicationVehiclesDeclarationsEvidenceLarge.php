<?php

declare(strict_types=1);

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\VehiclesDeclarationsEvidenceLarge;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Laminas\Form\Form;
use Laminas\Validator\ValidatorPluginManager;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

class ApplicationVehiclesDeclarationsEvidenceLarge extends VehiclesDeclarationsEvidenceLarge
{
    use ButtonsAlterations;

    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService, protected TranslationHelperService $translator, protected UrlHelperService $urlHelper, protected ValidatorPluginManager $validatorPluginManager)
    {
        parent::__construct($formHelper, $authService, $translator, $urlHelper, $validatorPluginManager);
    }

    /**
     * Make form alterations
     *
     * @param Form $form form
     *
     * @return Form
     */
    #[\Override]
    protected function alterForm($form)
    {
        parent::alterForm($form);
        $this->alterButtons($form);

        return $form;
    }
}
