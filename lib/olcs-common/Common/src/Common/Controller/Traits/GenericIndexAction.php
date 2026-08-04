<?php

/**
 * Generic Index Action
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Controller\Traits;

/**
 * Generic Index Action
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait GenericIndexAction
{
    /**
     * Render the section form
     *
     * @return \Laminas\Http\Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }
}
