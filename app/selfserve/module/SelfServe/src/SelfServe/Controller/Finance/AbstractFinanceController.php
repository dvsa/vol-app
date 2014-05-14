<?php

/**
 * Abstract Finance Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller\Finance;

use SelfServe\Controller\AbstractApplicationController;

/**
 * Abstract Finance Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractFinanceController extends AbstractApplicationController
{

    public function __construct()
    {
        $this->setCurrentSection('operating-centre');
    }

    /**
     * Render the layout

     * @param object $view
     * @param string $current
     */
    public function renderLayoutWithSubSections($view, $current = '', $journey = 'operating-centre', $disabled = null)
    {
        $applicationId = $this->getApplicationId();

        $this->setSubSections(
            array(
                'operatingCentre' => array(
                    'label' => 'selfserve-app-subSection-operating-centre-oc',
                    'route' => 'selfserve/finance/operating_centre',
                    'routeParams' => array(
                        'applicationId' => $applicationId
                    )
                ),
                'financialEvidence' => array(
                    'label' => 'selfserve-app-subSection-operating-centre-fe',
                    'route' => 'selfserve/finance/financial_evidence',
                    'routeParams' => array(
                        'applicationId' => $applicationId
                    )
                )
            )
        );

        return parent::renderLayoutWithSubSections($view, $current, $journey, $disabled);
    }
}
