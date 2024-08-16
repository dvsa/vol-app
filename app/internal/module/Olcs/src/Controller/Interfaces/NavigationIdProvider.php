<?php

/**
 * Navigation Id Provider
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Interfaces;

/**
 * Navigation Id Provider
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface NavigationIdProvider
{
    /**
     * get method Navigation Id
     *
     * @return string
     */
    public function getNavigationId();
}
