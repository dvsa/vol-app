<?php

namespace Dvsa\Olcs\Utils\Client;

use Olcs\Logging\Log\Logger;
use Laminas\Http\Client;
use Laminas\Http\Client\Adapter\AdapterInterface as HttpAdapter;
use Laminas\Http\Client\Adapter\StreamInterface;
use Laminas\Http\Response;

class ClientAdapterLoggingWrapper implements HttpAdapter, StreamInterface
{
    /** @var HttpAdapter|StreamInterface */
    private $adapter;

    private $host;
    private $port;
    private $shouldLogData = true;

    /**
     * Any adapter methods that don't exist in the interface will be wrapped
     *
     * @param string $method Call Method
     * @param array  $args   Method agruments
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->getAdapter(), $method], $args);
    }

    /**
     * Set adapter
     *
     * @param HttpAdapter $adapter Adapter
     *
     * @return $this
     */
    public function setAdapter(HttpAdapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Return native adapter
     *
     * @return HttpAdapter|StreamInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Wrap adapter of specified client
     *
     * @param Client $client Client
     *
     * @return void
     */
    public function wrapAdapter(Client $client)
    {
        $this->setAdapter($client->getAdapter());
        $client->setAdapter($this);
    }

    /**
     * Returns is should log data flag
     *
     * @return bool
     */
    public function getShouldLogData()
    {
        return $this->shouldLogData;
    }

    /**
     * Set, is response should be logged
     *
     * @param bool $shouldLogData True for should logged
     *
     * @return $this
     */
    public function setShouldLogData($shouldLogData = true)
    {
        $this->shouldLogData = $shouldLogData;
        return $this;
    }

    /**
     * Set the configuration array for the adapter.
     *
     * @param array $options Adapter options to set
     */
    #[\Override]
    public function setOptions($options = []): void
    {
        $this->getAdapter()->setOptions($options);
    }

    /**
     * Connect to the remote server
     *
     * @param string $host   Host Url
     * @param int    $port   Port
     * @param bool   $secure Use Secure connection
     *
     * @return void
     */
    #[\Override]
    public function connect($host, $port = 80, $secure = false)
    {
        $this->host = $host;
        $this->port = $port;
        Logger::debug('Client Connection: ' . $host . ':' . $port . ' (' . get_class($this->getAdapter()) . ')');

        $this->getAdapter()->connect($host, $port, $secure);
    }

    /**
     * Send request to the remote server
     *
     * @param string        $method  Method
     * @param \Laminas\Uri\Uri $url     Url
     * @param string        $httpVer Http protocol version
     * @param array         $headers Http Headers
     * @param string        $body    Body
     *
     * @return string
     */
    #[\Override]
    public function write($method, $url, $httpVer = '1.1', $headers = [], $body = '')
    {
        $data = [
            'data' => [
                'headers' => (array)$headers,
                'body' => $this->shouldLogData ? $body : '*** OMITTED ***'
            ]
        ];

        Logger::debug('Client Request: ' . $method . ' -> ' . $url, $data);

        return $this->getAdapter()->write($method, $url, $httpVer, $headers, $body);
    }

    /**
     * Read response from server
     *
     * @return string
     */
    #[\Override]
    public function read()
    {
        $response = $this->getAdapter()->read();
        $responseObject = Response::fromString($response);
        $data = [
            'data' => [
                'headers' => $responseObject->getHeaders()->toArray(),
                'body' => $this->shouldLogData ? $responseObject->getBody() : '*** OMITTED ***',
                'statusCode' => $responseObject->getStatusCode(),
            ]
        ];

        Logger::logResponse($responseObject->getStatusCode(), 'Client Response', $data);

        return $response;
    }

    /**
     * Close the connection to the server
     *
     * @return void
     */
    #[\Override]
    public function close()
    {
        Logger::debug('Close Connection:' . $this->host . ':' . $this->port);
        $this->host = null;

        $this->getAdapter()->close();
    }

    /**
     * Set output stream
     *
     * @param resource $stream Stream
     *
     * @return $this
     */
    #[\Override]
    public function setOutputStream($stream)
    {
        $this->adapter->setOutputStream($stream);
        return $this;
    }
}
