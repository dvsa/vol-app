<?php
/**
 * Resolves API names to API URL:s
 * 
 * @package     olcscommon
 * @subpackage  utility
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace OlcsCommon\Utility;

use Zend\Uri\Http as HttpUri;

/**
 * Resolves API names to API URL:s
 */
class ResolveApi
{
    /**
     * @var array
     */
    protected $mapping;

    /**
     * @param array $mapping The mapping of API:s to URL:s
     */
    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Creates and returns a client for a specific API URL
     *
     * @param string $api The name of an API
     * @return RestClient
     * @throws Exception
     */
    public function getClient($api)
    {
        if (!isset($this->mapping[$api])) {
            throw new \Exception('Invalid API name');
        }

        $url = new HttpUri($this->mapping[$api]['path']);
        $url->resolve($this->mapping[$api]['baseUrl']);

        return new RestClient($url);
    }
}
