<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\DocumentShare\Data\Object\File as ContentStoreFile;
use Dvsa\Olcs\Utils\Helper\FileHelper;
use Laminas\Http\Response\Stream;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerInterface;
use Laminas\Http\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Abstract class for download handler
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
abstract class AbstractDownload extends AbstractQueryHandler implements UploaderAwareInterface
{
    use UploaderAwareTrait;

    protected $repoServiceName = 'Document';

    private $isInline = false;
    /** @var array */
    private $config = [];

    /**
     * Process downloading file
     *
     * @throws NotFoundException
     */
    protected function download(string $identifier, ?string $path = null, ?string $chosenFileName = null): Stream
    {
        if ($path === null) {
            $path = $identifier;
        }

        $file = $this->getUploader()->download($path);

        if ($file === false) {
            $logInfo = [
                'identifier' => $identifier,
                'path' => $path,
                'filename' => $chosenFileName,
            ];

            Logger::info('File could not be downloaded', $logInfo);
            throw new NotFoundException();
        }

        $response = new Stream();
        $response->setStatusCode(Response::STATUS_CODE_200);

        $fileName = $file->getResource();
        $fileSize = $file->getSize();

        $response->setStream(fopen($fileName, 'rb'));
        $response->setStreamName($fileName);
        $response->setContentLength($fileSize);
        $response->setCleanup(true);

        $extension = FileHelper::getExtension($identifier);

        $isInline = (
            $this->isInline === true
            || 'html' === $extension
        );

        $downloadFileName = basename($identifier);

        // OLCS-14910 If file doesn't have an extension then add a '.txt' extension
        if (empty($extension)) {
            //used in case of the original identifier being used
            $downloadFileName .= '.txt';

            //used in the case of a user chosen filename
            $extension = 'txt';
        }

        if ($chosenFileName !== null) {
            $downloadFileName = $chosenFileName . '.' . $extension;
        }

        $headers = $response->getHeaders();
        $headers->addHeaders(
            [
                'Content-Type' => $this->getMimeType($file, $path) . ';charset=UTF-8',
                'Content-Length' => $fileSize,
                'Content-Disposition' => ($isInline ? 'inline' : 'attachment') .
                    ';filename="' . $downloadFileName . '"',
            ]
        );

        return $response;
    }

    /**
     * Setter for isInline property
     *
     * @param bool $inline True, if do not download
     *
     * @return $this
     */
    protected function setIsInline($inline)
    {
        $this->isInline = (bool)$inline;
        return $this;
    }

    /**
     * Define correct mimetype for file
     *
     * @param ContentStoreFile $file File
     * @param string           $path Path to file
     *
     * @return string
     */
    private function getMimeType(ContentStoreFile $file, $path)
    {
        $ext = FileHelper::getExtension($path);

        $cfgDs = $this->config['document_share'];
        $mimeExclude = ($cfgDs['invalid_defined_mime_types'] ?? []);

        return $mimeExclude[$ext] ?? $file->getMimeType();
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return AbstractDownload
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->config = (array)$container->get('config');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
