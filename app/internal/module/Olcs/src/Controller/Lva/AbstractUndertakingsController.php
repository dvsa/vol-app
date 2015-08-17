<?php

/**
 * Internal Abstract Undertakings Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractUndertakingsController as CommonAbstractUndertakingsController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
* Internal Abstract Undertakings Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractUndertakingsController extends CommonAbstractUndertakingsController
{
    protected function formatDataForForm($applicationData)
    {
        return array(
            'declarationsAndUndertakings' => array(
                'declarationConfirmation' => $applicationData['declarationConfirmation'],
                'version' => $applicationData['version'],
                'id' => $applicationData['id'],
            )
        );
    }
}
