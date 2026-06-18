<?php

namespace Common\Controller;

use Dvsa\Olcs\Transfer\Query\Document\Download;
use Dvsa\Olcs\Transfer\Query\Document\DownloadGuide;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController as LaminasAbstractActionController;

/**
 * File controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FileController extends LaminasAbstractActionController
{
    private static $allowedHeaders = ['Content-Disposition', 'Content-Encoding', 'Content-Type', 'Content-Length'];

    /**
     * Download a file
     *
     * @return Response\Stream|\Laminas\View\Model\ViewModel
     */
    public function downloadAction()
    {
        $identifier = $this->params()->fromRoute('identifier');
        $isInline = (bool)$this->params()->fromQuery('inline');
        $isSlug = (bool)$this->params()->fromQuery('slug');

        if (is_numeric($identifier)) {
            $query = Download::create(
                [
                    'identifier' => $identifier,
                    'isInline' => $isInline,
                    'isStream' => true,
                ]
            );
        } else {
            // if not a number then we assume it must be a guide document
            $query = DownloadGuide::create(
                [
                    'identifier' => base64_decode($identifier),
                    'isSlug' => $isSlug,
                    'isInline' => $isInline,
                    'isStream' => true,
                ]
            );
        }

        /** @var \Common\Service\Cqrs\Response $downloadResponse */
        $downloadResponse = $this->handleQuery($query);

        if (!$downloadResponse->isOk()) {
            throw new \RuntimeException('Error downloading file');
        }

        $response = $downloadResponse->getHttpResponse();

        //  keep only allowed headers
        $headers = new \Laminas\Http\Headers();
        foreach ($response->getHeaders() as $header) {
            if (in_array($header->getFieldName(), self::$allowedHeaders, true)) {
                $headers->addHeader($header);
            }
        }

        $response->setHeaders($headers);

        return $response;
    }
}
