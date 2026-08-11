<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\RetrievalLink;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\Document\AbstractDownload;
use Dvsa\Olcs\Api\Domain\Retrieval\RetrievalLinkAccessTrait;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLinkDocument as MemberEntity;
use Dvsa\Olcs\Api\Service\Retrieval\RetrievalPolicy;
use Dvsa\Olcs\Api\Service\Retrieval\SessionGrantService;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;

/**
 * Streams one bundle member. Authorises on the API side: the member must exist, its link must
 * match the presented token and still be usable, and — for otp-gated links — a valid post-OTP
 * session grant must be presented (defence-in-depth, so a direct download cannot bypass the OTP
 * step enforced by the selfserve page). Reuses AbstractDownload's content-store streaming.
 */
final class Download extends AbstractDownload
{
    use RetrievalLinkAccessTrait;

    protected $repoServiceName = 'RetrievalLinkDocument';

    protected $extraRepos = ['RetrievalLink', 'RetrievalLinkEvent'];

    private SessionGrantService $sessionGrantService;

    private int $presignedTtl = 300;

    /**
     * @param QueryInterface $query
     * @throws NotFoundException
     */
    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        $now = new \DateTimeImmutable();

        /** @var MemberEntity|null $member */
        $member = $this->getRepo()->fetchByMemberRef((string) $query->getMemberRef());
        if ($member === null) {
            throw new NotFoundException();
        }

        $link = $member->getRetrievalLink();

        // Member ref and token must belong to the SAME link, and that link must be usable.
        if (
            !hash_equals((string) $link->getToken(), (string) $query->getToken())
            || !$this->isLinkUsable($link, $now)
        ) {
            throw new NotFoundException();
        }

        if ($link->getGateMode() === RetrievalPolicy::GATE_OTP) {
            $grant = (string) $query->getGrant();
            if ($grant === '' || !$this->sessionGrantService->isValid($grant, (string) $link->getToken(), $now)) {
                $this->recordRetrievalEvent($link, 'denied', $member->getMemberRef(), null, null, 'invalid or missing session grant');
                throw new NotFoundException();
            }
        }

        $this->recordRetrievalEvent($link, 'downloaded', $member->getMemberRef(), null, null, $member->getDisplayFilename());

        $identifier = (string) $member->getDocument()->getIdentifier();

        // On an S3-backed store, hand selfserve a short-lived presigned URL so it fetches the bytes
        // straight from S3, keeping the API out of the byte path. WebDAV returns null → we stream.
        $presignedUrl = $this->getUploader()->presignedGetUrl($identifier, $this->presignedTtl);
        if ($presignedUrl !== null) {
            return [
                'presignedUrl' => $presignedUrl,
                'filename' => $member->getDisplayFilename(),
            ];
        }

        return $this->download(
            $identifier,
            null,
            pathinfo((string) $member->getDisplayFilename(), PATHINFO_FILENAME),
        );
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->sessionGrantService = $container->get(SessionGrantService::class);
        $config = $container->get('config');
        $this->presignedTtl = (int) ($config['retrieval']['presigned_ttl'] ?? 300);

        return parent::__invoke($container, $requestedName, $options);
    }
}
