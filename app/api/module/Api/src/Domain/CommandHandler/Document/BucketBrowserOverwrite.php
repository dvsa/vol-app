<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\Olcs\DocumentShare\Service\S3BucketBrowser;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * Super-admin S3 bucket browser: overwrite/create an object at a RAW key. Phase 2 — gated by BOTH
 * the S3_BUCKET_BROWSER and S3_BUCKET_BROWSER_OVERWRITE toggles (plus IsSystemAdmin in the
 * validation map) and audited. By design this does NOT update the document table: it is a raw
 * bucket tool, so document metadata (size/filename) is intentionally left untouched.
 */
final class BucketBrowserOverwrite extends AbstractCommandHandler implements ToggleRequiredInterface, AuthAwareInterface
{
    use ToggleAwareTrait;
    use AuthAwareTrait;

    protected $toggleConfig = [FeatureToggle::S3_BUCKET_BROWSER, FeatureToggle::S3_BUCKET_BROWSER_OVERWRITE];

    private S3BucketBrowser $bucketBrowser;

    /**
     * @param \Dvsa\Olcs\Transfer\Command\Document\BucketBrowserOverwrite $command
     */
    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        $key = (string) $command->getKey();
        $file = $this->buildFile($command->getContent());

        $response = $this->bucketBrowser->putObject($key, $file);

        if (!$response->isOk()) {
            throw new RuntimeException(
                sprintf('Failed to overwrite object "%s" (status %d)', $key, $response->getStatusCode())
            );
        }

        Logger::info('S3 bucket browser: overwrite', [
            'data' => ['userId' => $this->getUserId(), 'key' => $key, 'size' => $file->getSize()],
        ]);

        $this->result->addMessage('Object overwritten');
        $this->result->addId('key', $key);

        return $this->result;
    }

    /**
     * Build a File from the multipart upload field (tmp_name), with a base64 fallback — mirrors
     * Document\Upload::uploadFile.
     */
    private function buildFile(mixed $content): File
    {
        $file = new File();

        if (is_array($content) && !empty($content['tmp_name'])) {
            $file->setContentFromStream($content['tmp_name']);
            if ('application/octet-stream' === $file->getMimeType() && !empty($content['type'])) {
                $file->setMimeType($content['type']);
            }
        } else {
            // Strict decode: reject invalid base64 rather than silently writing corrupted/empty
            // bytes over the object (the overwrite path is destructive and doesn't touch metadata).
            $decoded = base64_decode((string) $content, true);
            if ($decoded === false) {
                throw new RuntimeException('Overwrite content is not valid base64-encoded data');
            }
            $file->setContent($decoded);
        }

        return $file;
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
