<?php

namespace Common\Service\Api;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * Class Resolver
 * @package Common\Service\Api
 * @template-extends AbstractPluginManager<object>
 */
class Resolver extends AbstractPluginManager
{
    protected $instanceOf;

    /**
     * @deprecated
     * @param $api
     * @return object
     */
    public function getClient($api)
    {
        return $this->get('Olcs\\RestService\\' . $api);
    }
}
