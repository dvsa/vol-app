<?php

/**
 * Abstract Finance Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller\Finance;

use Common\Controller\FormJourneyActionController;

/**
 * Abstract Finance Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractFinanceController extends FormJourneyActionController
{
    /**
     * Render the layout

     * @param object $view
     * @param string $current
     */
    protected function renderLayout($view, $current = '')
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        $subSections = array(
            array(
                'label' => 'selfserve-app-subSection-operating-centre-oc',
                'route' => 'selfserve/finance/operating_centre',
                'active' => ($current == 'operatingCentre'),
                'routeParams' => array(
                    'applicationId' => $applicationId
                )
            ),
            array(
                'label' => 'selfserve-app-subSection-operating-centre-fe',
                'route' => 'selfserve/finance/financial_evidence',
                'active' => ($current == 'financialEvidence'),
                'routeParams' => array(
                    'applicationId' => $applicationId
                )
            )
        );

        $layout = $this->getViewModel(
            array(
                'subSections' => $subSections
            )
        );

        $layout->setTemplate('self-serve/finance/layout');

        $layout->addChild($view, 'main');

        return $layout;
    }
}
