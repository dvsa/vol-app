<?php
/**
 * A trait that controllers can use to easily interact with service API:s
 *
 * @package     olcscommon
 * @subpackage  utility
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace OlcsCommon\Utility;

trait ResolveApiTrait
{
    /**
     * Creates and returns a client for a specific service API
     *
     * @param string $service The name of the service to return a client for
     * @return RestClient A client configured for the desired service
     */
    protected function service($service)
    {
        $serviceApiResolver = $this->getServiceLocator()->get('ServiceApiResolver');
        return $serviceApiResolver->getClient($service);
    }
}
