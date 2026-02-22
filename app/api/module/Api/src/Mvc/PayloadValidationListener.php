<?php

namespace Dvsa\Olcs\Api\Mvc;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Http\Header\ContentType;
use Laminas\Mvc\MvcEvent;
use Laminas\Router;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Http\Request as HttpRequest;
use Laminas\Http\Response;

class PayloadValidationListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    public const JSON_NOT_VALID_CODE = Response::STATUS_CODE_422;
    public const XML_NOT_VALID_CODE = Response::STATUS_CODE_400;

    public const JSON_MEDIA_TYPE = 'application/json';

    protected $xmlMediaTypes = ['text/xml', 'application/xml'];

    protected $annotationBuilder;

    public function __construct(AnnotationBuilder $annotationBuilder)
    {
        $this->annotationBuilder = $annotationBuilder;
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, $this->onRoute(...), $priority);
    }

    public function onRoute(MvcEvent $e)
    {
        $request = $e->getRequest();
        if (!($request instanceof HttpRequest)) {
            return;
        }

        $matches = $e->getRouteMatch();

        if (!$matches instanceof Router\RouteMatch) {
            // Can't do anything without a route match
            return;
        }

        $dtoClass = $matches->getParam('dto', false);

        if (!$dtoClass) {
            // no controller matched, nothing to do
            return;
        }

        $data = $matches->getParams();

        $dto = new $dtoClass();
        $matches->setParam('dto', $dto);

        if ($request->isGet()) {
            $data = array_merge($data, (array) $request->getQuery());
            $dto->exchangeArray($data);

            $query = $this->annotationBuilder->createQuery($dto);

            if (!$query->isValid()) {
                $response = new Response();
                $response->setStatusCode(Response::STATUS_CODE_422);
                $response->getHeaders()->addHeaders(['Content-Type' => 'application/json']);
                $response->setContent(json_encode($query->getMessages(), true));
                return $response;
            }
        } else {

            /**
             * @var ContentType $contentType
             * @var string $mediaType
             */
            $contentType = $request->getHeader('content-type');
            $mediaType = $contentType->getMediaType();
            $isXml = in_array($mediaType, $this->xmlMediaTypes);

            if ($mediaType === self::JSON_MEDIA_TYPE) {
                $data = json_decode((string) $request->getContent(), true);
            } elseif ($isXml) {
                $data = ['xml' => $request->getContent()];
            } else {
                $data = (array)$request->getPost();

                $files = $request->getFiles();

                foreach ($files as $fieldName => $fileData) {
                    $data[$fieldName] = $fileData;
                }
            }

            $dto->exchangeArray($data);
            $command = $this->annotationBuilder->createCommand($dto);

            if (!$command->isValid()) {
                $response = new Response();

                if ($isXml) {
                    //we don't have xml responses which need to return anything other than a status code
                    $response->setStatusCode(self::XML_NOT_VALID_CODE);
                } else {
                    $response->setStatusCode(self::JSON_NOT_VALID_CODE);
                    $response->getHeaders()->addHeaders(['Content-Type' => self::JSON_MEDIA_TYPE]);
                    $response->setContent(json_encode($command->getMessages(), true));
                }

                return $response;
            }
        }
    }
}
