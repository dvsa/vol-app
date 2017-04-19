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
     *
     * @param int $lvaId lvaId
     *
     * @return \Zend\Http\Response
     */
    protected function handleCancelRedirect($lvaId)
    {
        $this->getServiceLocator()->get('Helper\FlashMessenger')->addInfoMessage('flash-discarded-changes');

        return $this->reload();
    }

    /**
     * Get the current organisation id
     *
     * @return int
     */
    protected function getCurrentOrganisationId()
    {
        return $this->getLvaEntityService()->getOrganisation($this->getIdentifier())['id'];
    }

    /**
     * Wrapper method so we can extend this behaviour
     *
     * @param int $lvaId lvaId
     *
     * @return \Zend\Http\Response
     */
    protected function goToOverviewAfterSave($lvaId = null)
    {
        return $this->reload();
    }
}
