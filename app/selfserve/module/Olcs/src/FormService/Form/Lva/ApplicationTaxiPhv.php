<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\TaxiPhv as CommonTaxiPhv;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Taxi Phv
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationTaxiPhv extends CommonTaxiPhv
{
    use ButtonsAlterations;

    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;

    public function __construct(FormHelperService $formHelper, AuthorizationService $authService)
    {
        parent::__construct($formHelper, $authService);
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
