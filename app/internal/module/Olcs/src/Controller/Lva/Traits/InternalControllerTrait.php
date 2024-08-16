<?php

/**
 * Abstract Internal Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Traits;

use Dvsa\Olcs\Transfer\Query\Application\Application as AppQry;
use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicQry;

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
     * @return \Laminas\Http\Response
     */
    protected function handleCancelRedirect($lvaId)
    {
        $this->flashMessengerHelper->addInfoMessage('flash-discarded-changes');

        return $this->reload();
    }

    /**
     * Get the current organisation id
     * NOTE: this ony works for LVA controllers, don't try to use elsewhere
     *
     * @return int
     */
    protected function getCurrentOrganisationId()
    {
        if ($this->lva === 'licence') {
            $query = LicQry::create(['id' => $this->getIdentifier()]);
            $licence = $this->handleQuery($query)->getResult();

            return $licence['organisation']['id'];
        }

        $query = AppQry::create(['id' => $this->getIdentifier()]);
        $application = $this->handleQuery($query)->getResult();

        return $application['licence']['organisation']['id'];
    }

    /**
     * Wrapper method so we can extend this behaviour
     *
     * @param int $lvaId lvaId
     *
     * @return \Laminas\Http\Response
     */
    protected function goToOverviewAfterSave($lvaId = null)
    {
        return $this->reload();
    }
}
