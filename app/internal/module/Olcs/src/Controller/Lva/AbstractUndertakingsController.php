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
    /**
     * Shows a cut down version of the declarations form. The wording isn't
     * particularly appropriate for internal use but we need to allow users to
     * mark the section complete in order to grant an application.
     *
     * @see https://jira.i-env.net/browse/OLCS-4894
     */
    protected function getForm()
    {
        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createForm('Lva\ApplicationUndertakings');

        $this->getServiceLocator()->get('Helper\Form')->remove($form, 'interim');

        return $form;
    }

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
