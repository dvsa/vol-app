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
                $this->recordRetrievalEvent($link, 'denied', $member->getMemberRef());
                throw new NotFoundException();
            }
        }

        $this->recordRetrievalEvent($link, 'downloaded', $member->getMemberRef());

        return $this->download(
            (string) $member->getDocument()->getIdentifier(),
            null,
            pathinfo((string) $member->getDisplayFilename(), PATHINFO_FILENAME),
        );
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->sessionGrantService = $container->get(SessionGrantService::class);
        return parent::__invoke($container, $requestedName, $options);
    }
}
