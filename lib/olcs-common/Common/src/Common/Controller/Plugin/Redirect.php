<?php

/**
 * Redirect
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\Redirect as LaminasRedirect;
use Laminas\Stdlib\ArrayUtils;

/**
 * Redirect
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Redirect extends LaminasRedirect
{
    /**
     * Refresh ajax
     *
     * @return \Laminas\Http\Response
     */
    public function refreshAjax()
    {
        return $this->toRouteAjax(null, [], [], true);
    }

    /**
     * Refresh ajax
     *
     * @param string $route              Route
     * @param array  $params             Params
     * @param array  $options            Options
     * @param bool   $reuseMatchedParams Reuse matched params
     *
     * @return \Laminas\Http\Response
     */
    public function toRouteAjax($route = null, $params = [], $options = [], $reuseMatchedParams = false)
    {
        $controller = $this->getController();

        if (isset($options['fragment'])) {
            // OLCS-14936 - force reload of the page by adding something random to the URL
            $options = ArrayUtils::merge(
                $options,
                [
                    'query' => [
                        'reload' => time()
                    ]
                ]
            );
        }

        if ($controller->getRequest()->isXmlHttpRequest()) {
            $data = [
                'status' => 302,
                'location' => $controller->url()->fromRoute($route, $params, $options, $reuseMatchedParams)
            ];
            $this->getResponse()->getHeaders()->addHeaders(['Content-Type' => 'application/json']);
            $this->getResponse()->setContent(json_encode($data));
            return $this->getResponse();
        }
        return $this->toRoute($route, $params, $options, $reuseMatchedParams);
    }
}
