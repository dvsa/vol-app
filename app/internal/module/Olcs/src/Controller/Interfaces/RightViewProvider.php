<?php

/**
 * Right View Provider
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Interfaces;

/**
 * Right View Provider
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface RightViewProvider
{
    /**
     * get method right view
     *
     * @return \Laminas\View\Model\ViewModel|null
     */
    public function getRightView();
}
