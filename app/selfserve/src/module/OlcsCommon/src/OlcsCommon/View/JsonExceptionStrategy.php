<?php
/**
 * Generates JSON responses on exceptions
 *
 * @package     olcscommon
 * @subpackage  view
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace OlcsCommon\View;

use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\JsonModel;
use Zend\Mvc\View\Http\ExceptionStrategy;

class JsonExceptionStrategy extends ExceptionStrategy
{
    /**
     * Create an exception view model, and set the HTTP status code
     *
     * @todo   dispatch.error does not halt dispatch unless a response is
     *         returned. As such, we likely need to trigger rendering as a low
     *         priority dispatch.error event (or goto a render event) to ensure
     *         rendering occurs, and that munging of view models occurs when
     *         expected.
     * @param  MvcEvent $e
     * @return void
     */
    public function prepareExceptionViewModel(MvcEvent $e)
    {
        // Do nothing if no error in the event
        $error = $e->getError();
        if (empty($error)) {
            return;
        }

        // Do nothing if the result is a response object
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }

        switch ($error) {
            case Application::ERROR_CONTROLLER_NOT_FOUND:
            case Application::ERROR_CONTROLLER_INVALID:
            case Application::ERROR_ROUTER_NO_MATCH:
                // Specifically not handling these
                return;

            case Application::ERROR_EXCEPTION:
            default:
                $data = array(
                    'error' => \OlcsCommon\Controller\AbstractRestfulController::ERROR_UNKNOWN,
                );

                if ($this->displayExceptions()) {
                    $exception = $e->getParam('exception');
                    while ($exception) {
                        $data['exceptions'][] = array(
                            'class'   => get_class($exception),
                            'file'    => $exception->getFile(),
                            'line'    => $exception->getLine(),
                            'code'    => $exception->getCode(),
                            'message' => $exception->getMessage(),
                            'trace'   => $exception->getTraceAsString(),
                        );
                        $exception = $exception->getPrevious();
                    }
                }

                $e->setResult(new JsonModel($data));

                $response = $e->getResponse();
                if (!$response) {
                    $response = new HttpResponse();
                    $response->setStatusCode(500);
                    $e->setResponse($response);
                } else {
                    $statusCode = $response->getStatusCode();
                    if ($statusCode === 200) {
                        $response->setStatusCode(500);
                    }
                }

                break;
        }
    }
}
