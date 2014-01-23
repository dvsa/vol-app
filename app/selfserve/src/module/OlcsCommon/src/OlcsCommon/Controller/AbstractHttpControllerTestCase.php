<?php
/**
 * An abstract controller that all ordinary OLCS controllers' tests inherit from
 *
 * @package     olcscommon
 * @subpackage  controller
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace OlcsCommon\Controller;

use \Zend\Http\Headers;
use \Zend\Http\Header\ContentType;
use \Mockery as m;

abstract class AbstractHttpControllerTestCase extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{
    protected $resolverMock;
    protected $clientMock = array();

    public function setUp($noConfig = false)
    {
        if (!$noConfig) {
            $this->setApplicationConfig(
                include __DIR__ . '/../../../../../config/test/application.config.php'
            );
        }
        parent::setUp();

        $this->resolverMock = m::mock('OlcsCommon\Utility\ResolveApi');

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('ServiceApiResolver',  $this->resolverMock);
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * Mocks a specific restful service
     *
     * Sets the service to be expected to be called at least once
     *
     * @param  string $service    The name of the service to mock
     * @param  string $method     The name of the service request to mock
     * @param  mixed  $response   The response of the mocked service or null of no response
     * @param  mixed  $parameters The expected request parameters for the mocked service, can eg. be \Mockery::any()
     * @return \Mockery\Expectation The configured Mockery expectation for the request
     */
    protected function mockService($service, $method, $response = null)
    {
        if (!isset($this->clientMock[$service])) {
            $this->clientMock[$service] =  m::mock('Olcs\Utility\RestClient');
            $this->resolverMock
                ->shouldReceive('getClient')
                ->with($service)
                ->andReturn($this->clientMock[$service]);
        }

        $expectation = $this->clientMock[$service]->shouldReceive($method)->once();
        if ($response !== null) {
            $expectation->andReturn($response);
        }

        return $expectation;
    }

    /**
     * Mocks a native PHP service
     *
     * @param  string $serviceName  The name of the service to mock
     * @param  string $serviceClass The class of the service to mock
     * @return \Mockery\MockInterface The mocked service
     */
    protected function mockNativeService($serviceName, $serviceClass)
    {
        $serviceMock = m::mock($serviceClass);

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService($serviceName, $serviceMock);

        return $serviceMock;
    }

    /**
     * Dispatch the MVC with an URL and a body
     *
     * @param  string       $url
     * @param  string       $method      HTTP Method to use
     * @param  string|array $body        The body or, if JSON, an array to encode as JSON
     * @param  string       $contentType The Content-Type HTTP header to set
     * @throws \Exception
     */
    public function dispatchBody($url, $method, $body, $contentType = 'application/json')
    {
        if (!is_string($body) && $contentType == 'application/json') {
            $body = json_encode($body);
        }

        $this->url($url, $method);

        $request = $this->getRequest();
        $request->setContent($body);

        $headers = new Headers();
        $headers->addHeader(ContentType::fromString('Content-Type: ' . $contentType));
        $request->setHeaders($headers);

        $this->getApplication()->run();

        if (true !== $this->traceError) {
            return;
        }

        $exception = $this->getApplication()->getMvcEvent()->getParam('exception');
        if ($exception instanceof \Exception) {
            throw $exception;
        }
    }
}
