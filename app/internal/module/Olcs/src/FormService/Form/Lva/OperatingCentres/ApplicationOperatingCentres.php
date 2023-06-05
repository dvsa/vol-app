<?php

namespace Olcs\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\Lva\OperatingCentres\AbstractOperatingCentres;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableFactory;
use Laminas\Form\Form;
use ZfcRbac\Service\AuthorizationService;

/**
 * Application Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationOperatingCentres extends AbstractOperatingCentres
{
    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;
    protected $tableBuilder;
    protected FormServiceManager $formServiceLocator;

    public function __construct(
        FormHelperService $formHelper,
        AuthorizationService $authService,
        $tableBuilder,
        FormServiceManager $formServiceLocator
    ) {
        $this->formHelper = $formHelper;
        $this->authService = $authService;
        $this->tableBuilder = $tableBuilder;
        $this->formServiceLocator = $formServiceLocator;
    }

    protected function alterForm(Form $form, array $params)
    {
        $this->formServiceLocator->get('lva-application')->alterForm($form);

        parent::alterForm($form, $params);
    }

    /**
     * @see AbstractOperatingCentres::allowChangingTrafficArea
     */
    protected function allowChangingTrafficArea($trafficAreaId)
    {
        // Traffic area can be changed as long as its not Northern Irelend
        return ($trafficAreaId !== RefData::NORTHERN_IRELAND_TRAFFIC_AREA_CODE);
    }

    /**
     * @see AbstractOperatingCentres::removeTrafficAreaElements
     */
    protected function removeTrafficAreaElements($data)
    {
        return false;
    }
}
