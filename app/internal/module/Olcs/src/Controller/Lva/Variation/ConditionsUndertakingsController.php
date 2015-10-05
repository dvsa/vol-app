<?php

/**
 * Internal Variation Conditions Undertakings Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Olcs\Controller\Interfaces\VariationControllerInterface;

/**
 * Internal Variation Conditions Undertakings Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionsUndertakingsController extends Lva\AbstractConditionsUndertakingsController implements
    VariationControllerInterface
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

        $response = $this->handleCommand(
            \Dvsa\Olcs\Transfer\Command\Variation\RestoreListConditionUndertaking::create(
                ['id' => $this->getIdentifier(), 'ids' => $ids]
            )
        );

        $flashMessenger = $this->getServiceLocator()->get('Helper\FlashMessenger');
        if ($response->isOk()) {
            if (count($response->getResult()['messages'])) {
                $flashMessenger->addSuccessMessage('generic-restore-success');
            } else {
                $flashMessenger->addInfoMessage('generic-nothing-updated');
            }
        } else {
            $flashMessenger->addErrorMessage('unknown-error');
        }

        return $this->redirect()->toRouteAjax(
            null,
            array($this->getIdentifierIndex() => $this->getIdentifier())
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    protected function getRenderVariables()
    {
        return array('title' => null);
    }
}
