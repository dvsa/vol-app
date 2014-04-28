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
     */
    protected function renderLayout($view)
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        $layout = $this->getViewModel(array('applicationId' => $applicationId));

        $layout->addChild($view, 'main');

        return $layout;
    }
}
