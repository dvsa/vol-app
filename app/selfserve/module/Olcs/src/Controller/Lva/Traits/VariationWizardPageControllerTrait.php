<?php

namespace Olcs\Controller\Lva\Traits;

use Common\Controller\Plugin\Redirect;
use Common\Service\Cqrs\Response as CqrsResponse;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Zend\Http\Request;
use Zend\Http\Response;

/**
 * Trait for use in an AbstractController that forms part of a variation wizard
 * @method CqrsResponse handleCommand(CommandInterface $query)
 * @method Request getRequest()
 * @method Redirect redirect()
 */
trait VariationWizardPageControllerTrait
{
    use ApplicationControllerTrait;

    /**
     * Get the variation type upon which controllers using this trait can operate
     *
     * @see RefData::VARIATION_TYPE_DIRECTOR_CHANGE for example
     *
     * @return string
     */
    abstract protected function getVariationType();

    /**
     * Fetch Data for Lva
     *
     * @see AbstractController::fetchDataForLva which will typically provide the implementation
     *
     * @return array|mixed
     */
    abstract protected function fetchDataForLva();

    /**
     * Get the initial wizard start location
     *
     * @see consuming class to provide implementation
     *
     * @return mixed
     */
    abstract protected function getStartRoute();

    /**
     * Ensure this controller is being called with a suitable variation
     *
     * @return null|mixed
     */
    protected function preDispatch()
    {
        if ($this->isApplicationNew()) {
            return $this->notFoundAction();
        }
        if ($this->fetchDataForLva()['variationType']['id'] !== $this->getVariationType()) {
            return $this->notFoundAction();
        }
        return null;
    }

    public function indexAction()
    {
        $formActions = $this->getRequest()->getPost('form-actions');
        if (is_array($formActions) and array_key_exists('cancel',$formActions)) {
            return $this->handleCancelRedirect();
        }
        return parent::indexAction();
    }

    /**
     * Handle the cancel action and return to start
     *
     * @return Response
     */
    protected function handleCancelRedirect()
    {
        $route = $this->getStartRoute();
        return $this->redirect()->toRoute(
            $route['name'],
            $route['params']
        );
    }
}
