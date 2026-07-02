<?php

/**
 * A client for Restful API:s over HTTP
 *
 * @author Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Common\Util;

use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use Laminas\Http\Client as HttpClient;
use Laminas\Http\Header\AcceptLanguage;
use Laminas\Http\Header\Cookie;
use Laminas\Http\Request;
use Laminas\Uri\Http as HttpUri;
use Laminas\Http\Header\Accept;
use Common\Util\RestClient\Exception;
use Common\Util\ResponseHelper;

/**
 * A client for Restful API:s over HTTP
 *
 * @author Pelle Wessman <pelle.wessman@valtech.se>
 */
class RestClient
{
    /**
     * @var HttpUri
     */
    public $url;

    /**
     * @var HttpClient
     */
    public $client;

    /**
     * Language to add to an accept-language header
     *
     * @var string
     */
    public $language = 'en-gb';

    /**
     * @var
     */
    protected $responseHelper;

    /**
     * @var Cookie
     */
    private $cookie;

    private ?string $authHeader = null;

    /**
     * @param array $options options passed to HttpClient
     * @param array $auth authentication username/password
     * @internal param \Common\Util\The $HttpUri URL of the resource that this client is meant to act on
     */
    public function __construct(HttpUri $url, $options = [], $auth = [], Cookie $cookie = null)
    {
        $defaultOptions = [
            'keepalive' => true,
            'timeout' => 30,
        ];

        $options = array_merge($defaultOptions, $options);

        $this->url = $url;
        $this->client = new HttpClient(null, $options);

        $adapter = new ClientAdapterLoggingWrapper();
        $adapter->wrapAdapter($this->client);

        if (!empty($auth)) {
            $this->client->setAuth(
                $auth['username'],
                $auth['password']
            );
        }

        if (!$cookie instanceof \Laminas\Http\Header\Cookie) {
            $cookie = new Cookie();
        }

        $this->cookie = $cookie;
    }

    /**
     * @param string $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = str_replace('_', '-', $language);
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Returns the URL for a resource
     *
     * @param null $path
     * @return string
     */
    public function url($path = null)
    {
        [$path] = $this->pathOrParams($path);
        return $this->url->toString() . $path;
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
    public function create($path = null, array $params = [])
    {
        return $this->post($path, $params);
    }

    /**
     * POST:s a body to a resource
     *
     * @param string|array $path   The subpath of the resource or if no subpath the parameters
     * @param array        $params The parameters of the request body
     * @return mixed       Returns the body of a successful request or false if not found
     * @throws Exception   Whenever the request fails
     */
    public function post($path = null, array $params = [])
    {
        [$path, $params] = $this->pathOrParams($path, $params);
        return $this->request('POST', $path, $params);
    }

    /**
     * Fetches data from a resource
     *
     * @param string|array $path   The subpath of the resource or if no subpath the parameters
     * @param array        $params The parameters of the request
     * @return mixed       Returns the body of a successful request or false if not found
     */
    public function read($path = null, array $params = [])
    {
        return $this->get($path, $params);
    }

    /**
     * Fetches data from a resource
     *
     * @param string|array $path   The subpath of the resource or if no subpath the parameters
     * @param array        $params The parameters of the request
     * @return mixed       Returns the body of a successful request or false if not found
     * @throws Exception   Whenever the request fails
     */
    public function get($path = null, array $params = [])
    {
        [$path, $params] = $this->pathOrParams($path, $params);
        return $this->request('GET', $path, $params);
    }

    /**
     * Updates a resource
     *
     * @param string|array $path   The subpath of the resource or if no subpath the parameters
     * @param array        $params The parameters of the request
     * @return mixed       Returns the body of a successful request or false if not found
     */
    public function update($path = null, array $params = [])
    {
        return $this->put($path, $params);
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
    public function put($path = null, array $params = [])
    {
        [$path, $params] = $this->pathOrParams($path, $params);
        return $this->request('PUT', $path, $params);
    }

    /**
     * Partially update a resource
     *
     * Does a PATCH-request to the resource using the supplied parameters
     *
     * @param string|array $path   The subpath of the resource or if no subpath the parameters
     * @param array        $params The parameters of the request body
     * @return mixed       Returns the body of a successful request or false if not found
     * @throws Exception   Whenever the request fails
     */
    public function patch($path = null, array $params = [])
    {
        [$path, $params] = $this->pathOrParams($path, $params);
        return $this->request('PATCH', $path, $params);
    }

    /**
     * Fetches data from a resource
     *
     * @param string|array $path   The subpath of the resource or if no subpath the parameters
     * @param array        $params The parameters of the request
     * @return mixed       Returns the body of a successful request or false if not found
     */
    public function delete($path = null, array $params = [])
    {
        [$path, $params] = $this->pathOrParams($path, $params);
        return $this->request('DELETE', $path, $params);
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
    public function request($method, $path, array $params = [])
    {
        $this->prepareRequest($method, $path, $params);

        $response = $this->client->send();
        $responseHelper = $this->getResponseHelper();

        $responseHelper->setMethod($method);
        $responseHelper->setResponse($response);
        $responseHelper->setParams($params);
        return $responseHelper->handleResponse();
    }

    public function setResponseHelper(ResponseHelper $helper): static
    {
        $this->responseHelper = $helper;
        return $this;
    }

    public function getResponseHelper()
    {
        if (null === $this->responseHelper) {
            $this->setResponseHelper(new ResponseHelper());
        }

        return $this->responseHelper;
    }

    /**
     * Configures the HTTP client for the request
     *
     * @see RestClient::request()
     */
    public function prepareRequest(string $method, string $path, array $params = []): void
    {
        $method = strtoupper($method);

        $accept = $this->getAccept();
        $accept->addMediaType('application/json');

        $acceptLanguage = $this->getAcceptLanguage();
        $acceptLanguage->addLanguage($this->getLanguage());

        $this->client->resetParameters(true);
        $this->client->setRequest($this->getClientRequest());

        $this->client->setUri($this->url->toString() . $path);

        $headers = [$accept, $acceptLanguage, $this->cookie];
        if (!is_null($this->authHeader)) {
            $headers[] = $this->authHeader;
        }

        $this->client->setHeaders($headers);

        $this->client->setMethod($method);

        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
            $this->client->setEncType('application/json');
            $this->client->setRawBody(json_encode($params));
        } else {
            $this->client->getRequest()->getQuery()->fromArray($params);
        }
    }

    public function getAccept(): Accept
    {
        return new Accept();
    }

    public function getAcceptLanguage(): AcceptLanguage
    {
        return new AcceptLanguage();
    }

    public function getClientRequest(): Request
    {
        return new Request();
    }

    public function setAuthHeader(string $header): void
    {
        $this->authHeader = $header;
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
        } elseif (empty($args[0])) {
            $args[0] = '';
        } elseif ($args[0][0] !== '/') {
            $args[0] = '/' . $path;
        }

        return $args;
    }
}
