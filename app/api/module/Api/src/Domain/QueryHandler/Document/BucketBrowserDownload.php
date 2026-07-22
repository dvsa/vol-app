<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\DocumentShare\Service\S3BucketBrowser;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Utils\Helper\FileHelper;
use Laminas\Http\Response;
use Laminas\Http\Response\Stream;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerInterface;

/**
 * Super-admin S3 bucket browser: download an object by its raw S3 key, streamed (proxied) through
 * the API so the access is gated and audited (no presigned URLs). Always served as an attachment
 * with nosniff so arbitrary objects can't render/execute inline. Gated by the S3_BUCKET_BROWSER
 * toggle (+ IsSystemAdmin in the validation map).
 */
class BucketBrowserDownload extends AbstractQueryHandler implements ToggleRequiredInterface, AuthAwareInterface
{
    use ToggleAwareTrait;
    use AuthAwareTrait;

    protected $toggleConfig = [FeatureToggle::S3_BUCKET_BROWSER];

    private S3BucketBrowser $bucketBrowser;

    /**
     * @param \Dvsa\Olcs\Transfer\Query\Document\BucketBrowserDownload $query
     * @throws NotFoundException
     */
    #[\Override]
    public function handleQuery(QueryInterface $query): Stream
    {
        $key = (string) $query->getKey();
        $file = $this->bucketBrowser->getObject($key);

        if ($file === false) {
            Logger::info('S3 bucket browser: download not found', [
                'data' => ['userId' => $this->getUserId(), 'key' => $key],
            ]);
            throw new NotFoundException();
        }

        Logger::info('S3 bucket browser: download', [
            'data' => ['userId' => $this->getUserId(), 'key' => $key, 'size' => $file->getSize()],
        ]);

        $response = new Stream();
        $response->setStatusCode(Response::STATUS_CODE_200);

        $fileName = $file->getResource();
        $fileSize = $file->getSize();

        $response->setStream(fopen($fileName, 'rb'));
        $response->setStreamName($fileName);
        $response->setContentLength($fileSize);
        $response->setCleanup(true);

        $downloadFileName = basename($key);
        $extension = FileHelper::getExtension($key);
        if (empty($extension)) {
            $downloadFileName .= '.txt';
        }

        // This is a raw bucket tool serving arbitrary, possibly attacker-supplied objects: always
        // force a download (never inline) and disable content-type sniffing, so an object that
        // sniffs as HTML/SVG can't execute in the internal app's origin. Sanitise the filename for
        // the quoted value (S3 keys may contain quotes/backslashes/control chars) and add an
        // RFC 5987 filename* carrying the exact name.
        $safeFileName = preg_replace('/[\x00-\x1f\x7f"\\\\]/', '_', $downloadFileName);

        $response->getHeaders()->addHeaders([
            'Content-Type' => $file->getMimeType() . ';charset=UTF-8',
            'Content-Length' => $fileSize,
            'Content-Disposition' => sprintf(
                "attachment;filename=\"%s\";filename*=UTF-8''%s",
                $safeFileName,
                rawurlencode($downloadFileName)
            ),
            'X-Content-Type-Options' => 'nosniff',
        ]);

        return $response;
    }

    private function getUserId(): ?int
    {
        $user = $this->getCurrentUser();
        return $user ? $user->getId() : null;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->bucketBrowser = $container->get(S3BucketBrowser::class);
        return parent::__invoke($container, $requestedName, $options);
    }
}
