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

        $mimeType = $this->getMimeType($file, $path);

        $headers = $response->getHeaders();
        $headers->addHeaders(
            [
                'Content-Type' => $mimeType . ';charset=UTF-8',
                'Content-Length' => $fileSize,
                'Content-Disposition' => ($isInline ? 'inline' : 'attachment') .
                    ';filename="' . $downloadFileName . '"',

                // Applies to every document, whatever the type. This is what stops a browser
                // sniffing a stored .rtf, .txt or image as HTML and rendering it as a document —
                // which is the route by which a non-HTML upload becomes an HTML one.
                'X-Content-Type-Options' => 'nosniff',
            ]
        );

        if (self::executesInOwnOrigin($mimeType, $extension)) {
            // 'sandbox' puts the document in a unique opaque origin. That is the load-bearing
            // part: injected script can no longer read cookies, storage, or make same-origin
            // requests as the viewer. The document itself stays readable.
            //
            // NEVER add allow-same-origin here. Combined with allow-scripts it re-grants the
            // real origin and is equivalent to having no sandbox at all. It looks harmless in
            // a diff, which is exactly why it is called out.
            //
            // allow-scripts is present, rather than the stricter no-token form, because the
            // snapshot layouts use href="javascript:window.print()" for their Print link and
            // already-stored snapshots cannot be re-rendered. The no-token form would break
            // Print on every historical document.
            $headers->addHeaderLine('Content-Security-Policy', 'sandbox allow-scripts');
        }

        return $response;
    }

    /**
     * Whether this content type, if the browser renders it, executes script in *our* origin.
     *
     * Only those types get the sandbox CSP. The store holds mostly rtf, images, pdf and office
     * documents; none of those can script in the origin once nosniff is set, and sandboxing them
     * buys nothing. It is not free either — a sandbox CSP on an inline PDF can stop the browser's
     * built-in viewer rendering it, and caseworkers open PDFs inline deliberately. PDF script runs
     * in the viewer's own sandbox, not the page origin, so it is not the exposure being closed
     * here.
     *
     * SVG and XML are included alongside HTML: an SVG served as image/svg+xml and opened directly
     * is a document, and script inside it runs in the serving origin.
     */
    private static function executesInOwnOrigin(string $mimeType, ?string $extension): bool
    {
        $scriptableMimeTypes = [
            'text/html',
            'application/xhtml+xml',
            'image/svg+xml',
            'text/xml',
            'application/xml',
        ];

        $scriptableExtensions = ['html', 'htm', 'xhtml', 'svg', 'xml'];

        $baseMimeType = strtolower(trim(explode(';', $mimeType)[0]));

        return in_array($baseMimeType, $scriptableMimeTypes, true)
            || in_array(strtolower((string)$extension), $scriptableExtensions, true);
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
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $fullContainer = $container;

        $this->config = (array)$container->get('config');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
