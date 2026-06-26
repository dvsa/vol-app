<?php

/**
 * Generic Section Index Action
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Controller\Traits;

/**
 * Generic Section Index Action
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait GenericSectionIndexAction
{
    /**
     * Redirect to the first section
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->goToFirstSubSection();
    }
}
