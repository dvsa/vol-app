<?php
/**
 * A client for Restful API:s over HTTP
 * 
 * @package     olcscommon
 * @subpackage  utility
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace OlcsCommon\Utility;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Uri\Http as HttpUri;
use Zend\Http\Header\Accept;
use Zend\Stdlib\ParametersInterface;
use OlcsCommon\Utility\RestClient\Exception;

/**
 * A client for Restful API:s over HTTP
 */
class RestClient
{
    /**
     * @var HttpUri
     */
    protected $url;
    /**
     * @var HttpClient
     */
    protected $client;

    /**
     * @param HttpUri The URL of the resource that this client is meant to act on
     */
    public function __construct(HttpUri $url)
    {
        $this->url = $url;
        $this->client = new HttpClient();
    }

    /**
     * Returns the URL for a resource
     *
     * @return string
     */
    public function url($path = null)
    {
        list($path) = $this->pathOrParams($path);
        return $this->url->toString() . $path;
    }

    /**
     * Fetches data from a resource
     *
     * @param string|array $path   The subpath of the resource or if no subpath the parameters
     * @param array        $params The parameters of the request
     * @return mixed       Returns the body of a successful request or false if not found
     * @throws Exception   Whenever the request fails
     */
    public function get($path = null, array $params = array())
    {
        list($path, $params) = $this->pathOrParams($path, $params);
        return $this->request('GET', $path, $params);
    }

    /**
     * Creates a resource
     *
     * Does a POST-request to the resource using the supplied parameters
     *
     * @param string|array $path   The subpath of the resource or if no subpath the parameters
     * @param array        $params The parameters of the request body
     * @return mixed       Returns the body of a successful request or false if not found
     * @throws Exception   Whenever the request fails
     */
    public function create($path = null, array $params = array())
    {
        return $this->post($path, $params);
    }

    /**
     * Updates a resource
     *
     * Does a PATCH-request to the resource using the supplied parameters
     *
     * @param string|array $path   The subpath of the resource or if no subpath the parameters
     * @param array        $params The parameters of the request body
     * @return mixed       Returns the body of a successful request or false if not found
     * @throws Exception   Whenever the request fails
     */
    public function update($path = null, array $params = array(), $version = null)
    {
        list($path, $params, $version) = $this->pathOrParams($path, $params, $version);

        $this->prepareRequest('PATCH', $path, $params);

        if ($version) {
            $this->client->getRequest()->getQuery()->fromArray(array(
                'version' => $version,
            ));
        }

        $response = $this->client->send();

        if ($response->isNotFound()) {
            return false;
        } else if (!$response->isSuccess()) {
            throw new Exception('Error in HTTP response', $response->getStatusCode());
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * POST:s a body to a resource
     *
     * @param string|array $path   The subpath of the resource or if no subpath the parameters
     * @param array        $params The parameters of the request body
     * @return mixed       Returns the body of a successful request or false if not found
     * @throws Exception   Whenever the request fails
     */
    public function post($path = null, array $params = array())
    {
        list($path, $params) = $this->pathOrParams($path, $params);
        return $this->request('POST', $path, $params);
    }

    /**
     * Replaces or creates a resource
     *
     * Does a PUT-request to the resource using the supplied parameters
     *
     * @param string|array $path   The subpath of the resource or if no subpath the parameters
     * @param array        $params The parameters of the request body
     * @return mixed       Returns the body of a successful request or false if not found
     * @throws Exception   Whenever the request fails
     */
    public function put($path = null, array $params = array())
    {
        list($path, $params) = $this->pathOrParams($path, $params);
        return $this->request('PUT', $path, $params);
    }

    /**
     * Makes a HTTP request
     *
     * @param  string $method HTTP method to use
     * @param  string $path   The subpath of the resource that the request is meant for
     * @param  array  $params The parameters to include in the request
     * @return mixed          Returns the body of a successful request or false if not found
     * @throws Exception      Whenever the request fails
     */
    protected function request($method, $path, array $params = array())
    {
        $this->prepareRequest($method, $path, $params);

        $response = $this->client->send();

        if ($response->isNotFound()) {
            return false;
        } else if (!$response->isSuccess()) {
            throw new Exception('Error in HTTP response', $response->getStatusCode());
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * Configures the HTTP client for the request
     *
     * @see RestClient::request()
     */
    protected function prepareRequest($method, $path, array $params = array())
    {
        $method = strtoupper($method);

        $accept = new Accept();
        $accept->addMediaType('application/json');

        $this->client->setRequest(new Request());
        $this->client->setUri($this->url->toString() . $path);
        $this->client->setHeaders(array(
            $accept,
        ));
        $this->client->setMethod($method);

        if ($method == 'POST' || $method == 'PUT' || $method == 'PATCH') {
            $this->client->setEncType('application/json');
            $this->client->setRawBody(json_encode($params));
        } else {
            $this->client->getRequest()->getQuery()->fromArray($params);
        }
    }

    /**
     * Utility method to resolve method parameters
     *
     * @param  string|array $path   The subpath of the resource or if no subpath the parameters
     * @param  array        $params The parameters of the request body
     * @return array                First key is the path parameter, second is the params parameter
     */
    protected function pathOrParams($path, array $params = null)
    {
        $args = func_get_args();
        if (is_array($args[0])) {
            array_unshift($args, '');
        } else if (empty($args[0])) {
            $args[0] = '';
        } else if ($args[0][0] !== '/') {
            $args[0] = '/' . $path;
        }
        return $args;
    }
}
