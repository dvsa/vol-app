<?php

namespace Dvsa\Olcs\Db\Controller;

use Olcs\Logging\Log\Logger;
use Laminas\Mvc\Controller\AbstractRestfulController as LaminasAbstractRestfulController;
use Laminas\Mvc\Exception;
use Laminas\Mvc\MvcEvent;
use Laminas\Http\Response;
use Dvsa\Olcs\Db\Exceptions\RestResponseException;
use Dvsa\Olcs\Db\Traits\RestResponseTrait;

/**
 * Abstract Controller
 *
 * @author someone <someone@valtech.co.uk>
 */
abstract class AbstractController extends LaminasAbstractRestfulController
{
    use RestResponseTrait;

    /**
     * Handle the request
     *
     * @param  MvcEvent $e
     * @return mixed
     * @throws Exception\DomainException if no route matches in event or invalid HTTP method
     */
    #[\Override]
    public function onDispatch(MvcEvent $e)
    {
        $data = [
            'data' => [
                'content' => $e->getRequest()->getContent()
            ]
        ];

        Logger::debug('*** Legacy Api Controller ***: ' . static::class, $data);

        $response = $this->doDispatch($e);

        if ($response instanceof Response) {
            $content = [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode()
            ];
        } else {
            $content = (array)$response;
        }

        $data = [
            'data' => [
                'response' => $content
            ]
        ];

        Logger::debug('*** Legacy Api Controller Response ***', $data);

        return $response;
    }

    public function doDispatch(MvcEvent $e)
    {
        try {
            return parent::onDispatch($e);
        } catch (RestResponseException $ex) {
            return $this->respond($ex->getCode(), $ex->getMessage());
        }
    }

    /**
     * Basic functionality for when a page is not available
     *
     * @return array
     */
    #[\Override]
    public function notFoundAction()
    {
        return $this->respond(Response::STATUS_CODE_404, 'Resource not found');
    }

    /**
     * Get Controller name from route
     *
     * @return string
     */
    public function getControllerName()
    {
        $controller = $this->formatControllerName(
            $this->params()->fromRoute('controller')
        );

        return $controller;
    }

    private function formatControllerName($controller)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $controller)));
    }
}
