<?php

/**
 * Abstract Variation Controller
 *
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
namespace Olcs\Controller\Variation;

use Olcs\Controller\Application\AbstractApplicationController;

/**
 * Abstract Variation Controller
 *
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
abstract class AbstractVariationController extends AbstractApplicationController
{
    /**
     * Lva
     *
     * @var string
     */
    protected $lva = 'variation';

    /**
     * Hook into the dispatch before the controller action is executed
     */
    protected function preDispatch()
    {
        $applicationId = $this->getApplicationId();

        if (!$this->isApplicationVariation($applicationId)) {
            return $this->notFoundAction();
        }

        return $this->checkForRedirect($applicationId);
    }
}
