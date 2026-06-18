<?php

/**
 * Generic Edit Action
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Controller\Traits;

/**
 * Generic Edit Action
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait GenericEditAction
{
    /**
     * Render the edit form
     *
     * @return Response
     */
    public function editAction()
    {
        return $this->renderSection();
    }
}
