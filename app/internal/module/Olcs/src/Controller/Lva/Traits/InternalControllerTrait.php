<?php

/**
 * Abstract Internal Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

/**
 * Abstract Internal Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait InternalControllerTrait
{
    /**
     * Discard changes and redirect back to the current route
     */
    protected function handleCancelRedirect($lvaId)
    {
        $this->getServiceLocator()->get('Helper\FlashMessenger')->addInfoMessage('flash-discarded-changes');

        return $this->reload();
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
        return $this->reload();
    }
}
