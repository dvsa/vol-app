<?php

/**
 * Redirect Util
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Util;

/**
 * Redirect Util
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Redirect
{
    protected $route;

    protected $params = [];

    protected $options = [];

    protected $useRouteMatch = false;

    protected $ajax = false;

    public function toRoute($route = null, $params = [], $options = [], $useRouteMatch = false): static
    {
        $this->route = $route;
        $this->params = $params;
        $this->options = $options;
        $this->useRouteMatch = $useRouteMatch;
        $this->ajax = false;

        return $this;
    }

    public function toRouteAjax($route = null, $params = [], $options = [], $useRouteMatch = false): static
    {
        $this->route = $route;
        $this->params = $params;
        $this->options = $options;
        $this->useRouteMatch = $useRouteMatch;
        $this->ajax = true;

        return $this;
    }

    public function refresh(): static
    {
        $this->route = null;
        $this->params = [];
        $this->options = [];
        $this->useRouteMatch = true;
        $this->ajax = false;

        return $this;
    }

    public function refreshAjax(): static
    {
        $this->route = null;
        $this->params = [];
        $this->options = [];
        $this->useRouteMatch = true;
        $this->ajax = true;

        return $this;
    }

    public function process($redirect)
    {
        $method = 'toRoute';

        if ($this->ajax) {
            $method .= 'Ajax';
        }

        return $redirect->$method($this->route, $this->params, $this->options, $this->useRouteMatch);
    }
}
