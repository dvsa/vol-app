<?php

namespace Common\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Page Id
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PageId extends AbstractHelper
{
    public function __construct(private string $routeMatchName, private string $action)
    {
    }

    /**
     * Return a page id for the current page, which can be used in the automated tests
     *
     * @return string
     */
    public function __invoke()
    {
        return sprintf('pg:%s:%s', $this->routeMatchName, $this->action);
    }
}
