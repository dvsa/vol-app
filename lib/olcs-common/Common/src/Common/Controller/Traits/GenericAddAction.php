<?php

/**
 * Generic Add Action
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Controller\Traits;

/**
 * Generic Add Action
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait GenericAddAction
{
    /**
     * Render the add form
     *
     * @return Response
     */
    public function addAction()
    {
        return $this->renderSection();
    }
}
