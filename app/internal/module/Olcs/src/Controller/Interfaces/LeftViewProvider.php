<?php

/**
 * Left View Provider
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Interfaces;

/**
 * Left View Provider
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface LeftViewProvider
{
    /**
     * get method get left View
     *
     * @return \Laminas\View\Model\ViewModel|null
     */
    public function getLeftView();
}
