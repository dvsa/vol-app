<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\DocumentShare\Service\S3BucketBrowser;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerInterface;

/**
 * Super-admin S3 bucket browser: list one delimiter-grouped level under a prefix, straight from S3.
 * Gated by the S3_BUCKET_BROWSER feature toggle (and IsSystemAdmin in the validation map). Every
 * call is audited via the structured logger.
 */
class BucketBrowserList extends AbstractQueryHandler implements ToggleRequiredInterface, AuthAwareInterface
{
    use ToggleAwareTrait;
    use AuthAwareTrait;

    protected $toggleConfig = [FeatureToggle::S3_BUCKET_BROWSER];

    private S3BucketBrowser $bucketBrowser;

    /**
     * @param \Dvsa\Olcs\Transfer\Query\Document\BucketBrowserList $query
     */
    #[\Override]
    public function handleQuery(QueryInterface $query): array
    {
        $prefix = (string) $query->getPrefix();

        $listing = $this->bucketBrowser->listByPrefix($prefix, $query->getContinuationToken() ?: null);

        Logger::info('S3 bucket browser: list', [
            'data' => [
                'userId' => $this->getUserId(),
                'prefix' => $prefix,
                'folders' => count($listing['folders']),
                'objects' => count($listing['objects']),
            ],
        ]);

        return ['result' => $listing];
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
