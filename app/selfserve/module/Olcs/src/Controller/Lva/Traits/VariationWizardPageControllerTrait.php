<?php

namespace Olcs\Controller\Lva\Traits;

use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQry;
use Common\Controller\Plugin\Redirect;
use Common\Service\Cqrs\Response as CqrsResponse;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Variation\DeleteVariation;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\Plugin\Params;

/**
 * Trait for use in an AbstractController that forms part of a variation wizard
 * @method CqrsResponse handleCommand(CommandInterface $query)
 * @method Request getRequest()
 * @method Redirect redirect()
 * @method Params params(string $param = null, mixed $default = null)
 */
trait VariationWizardPageControllerTrait
{
    use ApplicationControllerTrait;

    /**
     * Get the required previous sections
     *
     * @return array required previous sections or empty array
     */
    abstract protected function getRequiredSections();

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
     * Check if a button has been pressed
     *
     * @see AbstractController::isButtonPressed
     *
     * @param string $button button
     * @param array  $data   data
     *
     * @return bool
     */
    abstract protected function isButtonPressed($button, $data = []);

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
        if (!$this->checkAppStatus($this->getApplicationId())) {
            return $this->notFoundAction();
        }

        $variationId = $this->getApplicationId();
        $sectionsCompleted = $this->getCurrentVariationStatus($variationId);

        if ($this->hasCompleted($sectionsCompleted, $this->getRequiredSections()) === false) {
            $route = $this->getStartRoute();
            return $this->redirect()->toRoute(
                $route['name'],
                $route['params']
            );
        }
        if ($this->isButtonPressed('cancel') && $this->params('action') === 'index') {
            return $this->handleCancelRedirect();
        }
        return null;
    }

    /**
     * Handle the cancel action and return to start
     *
     * @return Response
     */
    protected function handleCancelRedirect()
    {
        $this->handleCommand(DeleteVariation::create(['id' => $this->getIdentifier()]))->getResult();
        return $this->redirectToStartRoute();
    }

    /**
     * Redirect to the route that started this wizard
     *
     * @return Response
     */
    protected function redirectToStartRoute()
    {
        $route = $this->getStartRoute();

        return $this->redirect()->toRoute(
            $route['name'],
            $route['params']
        );
    }

    /**
     * get the status of the current variation
     *
     * @param int $id the variation id
     *
     * @return mixed
     */
    protected function getCurrentVariationStatus($id)
    {
        $variationStatus = [];
        $dto = ApplicationQry::create(['id' => $id, 'validateAppCompletion' => true]);
        $response = $this->handleQuery($dto);
        if ($response->getStatusCode() === 200) {
            $variationStatus = $response->getResult()['applicationCompletion'];
        }
        return $variationStatus;
    }

    /**
     * hasCompleted checks if previous sections of the wizard flow have been completed
     *
     * @param array $sectionsCompleted sections already flagged completed from db
     * @param array $requiredSections  sections that must be completed
     *
     * @return bool
     *
     */
    protected function hasCompleted(array $sectionsCompleted, array $requiredSections)
    {
        if (empty($requiredSections)) {
            return true;
        }
        $sections = array_filter(
            $sectionsCompleted,
            function ($v, $k) use ($requiredSections) {
                return in_array($k, $requiredSections) && $v === 2;
            },
            ARRAY_FILTER_USE_BOTH
        );

        return count($sections) === count($requiredSections);
    }
}
