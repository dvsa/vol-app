<?php

/**
 * Url Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Helper;

use Laminas\View\HelperPluginManager;

/**
 * Url Helper Service
 *
 * @NOTE ZF2 has 2 different URL builder classes, one is a controller plugin, the other is a view helper.
 *  We have the requirement to build URLs outside of views and controllers, so this helper essentially wraps ZF2s url
 *  builder, but allows us to easily use it elsewhere.
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UrlHelperService
{
    public const EXTERNAL_HOST = 'selfserve';

    public const INTERNAL_HOST = 'internal';

    /** @var HelperPluginManager */
    protected $helperPluginManager;

    /** @var array */
    protected $config;

    /**
     * Create service instance
     *
     *
     * @return UrlHelperService
     */
    public function __construct(
        HelperPluginManager $helperPluginManager,
        array $config
    ) {
        $this->helperPluginManager = $helperPluginManager;
        $this->config = $config;
    }

    /**
     * Generates a URL based on a route
     *
     * @param  string             $route              RouteInterface name
     * @param  array              $params             Parameters to use in url generation, if any
     * @param  array|bool         $options            RouteInterface-specific options to use in url generation, if any.
     *                                                If boolean, and no fourth argument, used as $reuseMatchedParams.
     * @param  bool               $reuseMatchedParams Whether to reuse matched parameters
     * @return string
     */
    public function fromRoute($route = null, $params = [], $options = [], $reuseMatchedParams = false)
    {
        $url = $this->helperPluginManager->get('url');

        return $url($route, $params, $options, $reuseMatchedParams);
    }

    public function fromRouteWithHost(
        string $hostKey,
        string|null $route = null,
        $params = [],
        $options = [],
        $reuseMatchedParams = false
    ): string {
        $hostname = $this->getHostname($hostKey);
        // this method isn't compatible with the canonical option
        $options['use_canonical'] = false;

        return $hostname . $this->fromRoute($route, $params, $options, $reuseMatchedParams);
    }

    private function getHostname($key)
    {
        $config = $this->config['hostnames'];
        if (!isset($config[$key])) {
            throw new \RuntimeException("Hostname for '" . $key . "' not found");
        }

        return $config[$key];
    }
}
