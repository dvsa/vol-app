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
}
