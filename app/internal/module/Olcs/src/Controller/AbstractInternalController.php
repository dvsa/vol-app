<?php

/**
 * Abstract Internal Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller\AbstractLvaController;

/**
 * Abstract Internal Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractInternalController extends AbstractLvaController
{
    /**
     * Set the location
     *
     * @var string
     */
    protected $location = 'internal';

    /**
     * Check for redirect
     *
     * @param int $lvaId
     * @return null|\Zend\Http\Response
     */
    protected function checkForRedirect($lvaId)
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirect()->toRoute(null, array(), array(), true);
        }
    }

    /**
     * Get the current organisation id
     *
     * @todo This method needs implementing properly once we know the users journey to get here
     *
     * @return int
     */
    protected function getCurrentOrganisationId()
    {
        return 1;
    }

    /**
     * Wrapper method so we can extend this behaviour
     *
     * @return \Zend\Http\Response
     */
    protected function goToOverviewAfterSave($lvaId = null)
    {
        return $this->redirect()->toRoute(null, array(), array(), true);
    }
}
