<?php

/**
 * Generic Delete Action
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Controller\Traits;

/**
 * Generic Delete Action
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait GenericDeleteAction
{
    /**
     * Delete
     *
     * @return \Laminas\Http\Response
     */
    public function deleteAction()
    {
        return $this->delete();
    }
}
