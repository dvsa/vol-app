<?php

/**
 * Internal Variation Conditions Undertakings Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * Internal Variation Conditions Undertakings Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionsUndertakingsController extends Lva\AbstractConditionsUndertakingsController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';

    /**
     * @NOTE At the moment this method can only be called from variation
     *
     * @return ViewModel
     */
    public function restoreAction()
    {
        $id = $this->params('child_id');

        $ids = explode(',', $id);

        $hasRestored = false;

        foreach ($ids as $id) {

            $response = $this->getAdapter()->restore($id, $this->getIdentifier());

            if ($response) {
                $hasRestored = $response;
            }
        }

        if ($hasRestored) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage('generic-restore-success');
        } else {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addInfoMessage('generic-nothing-updated');
        }

        return $this->redirect()->toRouteAjax(
            null,
            array($this->getIdentifierIndex() => $this->getIdentifier())
        );
    }
}
