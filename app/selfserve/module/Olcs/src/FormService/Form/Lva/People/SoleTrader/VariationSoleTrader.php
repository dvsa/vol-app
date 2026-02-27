<?php

namespace Olcs\FormService\Form\Lva\People\SoleTrader;

use Common\FormService\Form\Lva\People\SoleTrader\VariationSoleTrader as CommonVariationSoleTrader;
use Common\Form\Form;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Common\Service\Lva\PeopleLvaService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation SoleTrader
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationSoleTrader extends CommonVariationSoleTrader
{
    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;
    protected PeopleLvaService $peopleLvaService;
    protected FormServiceManager $formServiceLocator;

    public function __construct(
        FormHelperService $formHelper,
        AuthorizationService $authService,
        PeopleLvaService $peopleLvaService,
        FormServiceManager $formServiceLocator
    ) {
        parent::__construct($formHelper, $authService, $peopleLvaService, $formServiceLocator);
    }

    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return Form
     */
    #[\Override]
    public function alterForm($form, array $params)
    {
        $form = parent::alterForm($form, $params);

        $this->removeStandardFormActions($form);

        return $form;
    }
}
