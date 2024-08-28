<?php

namespace Dvsa\Olcs\Api\Service\File;

use Dvsa\Olcs\DocumentShare\Data\Object\File as ContentStoreFile;
use Dvsa\Olcs\DocumentShare\Service\DocumentStoreInterface;
use Laminas\Http\Response;
use Laminas\Log\Logger;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Content Store File Uploader
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ContentStoreFileUploader implements FileUploaderInterface, FactoryInterface
{
    public const ERR_UNABLE_UPLOAD = 'Unable to store uploaded file: %s. Code: %s';

    /**
     * @var DocumentStoreInterface
     */
    private $contentStoreClient;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Upload file to remote storage
     *
     * @param string           $identifier File name on Storage
     * @param ContentStoreFile $file       Uploded File
     *
     * @return ContentStoreFile
     * @throws Exception
     * @throws MimeNotAllowedException
     */
    public function upload($identifier, ContentStoreFile $file)
    {
        $file->setIdentifier($identifier);

        $this->logger->err(__METHOD__, ['identifier' => $identifier, 'file' => $file]);

        $response = $this->write($identifier, $file);

        $this->logger->err(__METHOD__, ['response' => $response]);

        if ($response->isSuccess()) {
            return $file;
        }

        if ($response->getStatusCode() === Response::STATUS_CODE_415) {
            throw new MimeNotAllowedException();
        }

        throw new Exception(sprintf(self::ERR_UNABLE_UPLOAD, $response->getBody(), $response->getStatusCode()));
    }

    /**
     * Download file from remote storage
     *
     * @param string $identifier File name on storage
     *
     * @return ContentStoreFile|null
     */
    public function download($identifier)
    {
        return $this->contentStoreClient->read($identifier);
    }

    /**
     * Remove the file from remote storage
     *
     * @param string $identifier File name on storage
     *
     * @return Response
     */
    public function remove($identifier)
    {
        return $this->contentStoreClient->remove($identifier);
    }

    /**
     * Write file to remote storage
     *
     * @param string           $identifier File name of storage
     * @param ContentStoreFile $file       File
     *
     * @return Response
     */
    private function write($identifier, ContentStoreFile $file)
    {
        return $this->contentStoreClient->write($identifier, $file);
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->contentStoreClient = $container->get('ContentStore');
        $this->logger = $container->get('Logger');
        return $this;
    }
}
