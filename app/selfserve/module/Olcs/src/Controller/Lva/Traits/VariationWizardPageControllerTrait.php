<?php

namespace Olcs\Controller\Lva\Traits;

use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQry;
use Zend\Http\Response;

/**
 * Trait for use in an AbstractController that forms part of a variation wizard
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
        if (isset($this->previousSections) && !$this->hasCompleted($this->previousSections)) {
            $route = $this->getStartRoute();
            return $this->redirect()->toRoute(
                $route['name'],
                $route['params']
            );

        }
        return null;
    }

    /**
     * Handle the cancel action and return to start
     *
     * @return Response
     */
    protected function handleWizardCancel()
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
     * @param $id
     *
     * @return mixed
     */
    protected function getCurrentVariationStatus($id)
    {
        $dto = ApplicationQry::create(['id' => $id, 'validateAppCompletion' => true]);
        $response = $this->handleQuery($dto);

        return $response->getResult()['applicationCompletion'];
    }

    /**
     *
     * @param $sections
     *
     * @return bool
     */
    public function hasCompleted($previousSections, $sectionsCompleted)
    {
        $completed = false;

        if (!empty($previousSections)) {
            foreach ($previousSections as $keyToCheck) {
                if (in_array($keyToCheck, array_keys($sectionsCompleted))) {
                    if ($sectionsCompleted[$keyToCheck] === 2) {
                        $completed = true;
                    } else {
                        $completed = false;
                    }
                }
            }

        }

        return $completed;
    }


}
