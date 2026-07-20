<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Retrieval;

use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLinkEvent;

/**
 * Shared usability check + audit-event recording for the retrieval handlers. Consumers must be
 * command/query handlers with `getRepo()` and `'RetrievalLinkEvent'` in their `$extraRepos`.
 */
trait RetrievalLinkAccessTrait
{
    /**
     * A link is usable only while it exists, has not been revoked, and has not expired. Unknown,
     * revoked and expired are deliberately indistinguishable to callers (no existence oracle).
     */
    protected function isLinkUsable(?RetrievalLink $link, \DateTimeInterface $now): bool
    {
        if ($link === null) {
            return false;
        }

        if ($link->getRevokedAt(true) !== null) {
            return false;
        }

        $expiresAt = $link->getExpiresAt(true);

        return $expiresAt instanceof \DateTimeInterface && $expiresAt > $now;
    }

    protected function recordRetrievalEvent(
        RetrievalLink $link,
        string $eventType,
        ?string $memberRef = null,
        ?string $ip = null,
        ?string $userAgent = null,
    ): void {
        $event = new RetrievalLinkEvent();
        $event->setRetrievalLink($link);
        $event->setEventType($eventType);
        $event->setMemberRef($memberRef);
        $event->setIp($ip);
        $event->setUserAgent($userAgent !== null ? substr($userAgent, 0, 255) : null);

        $this->getRepo('RetrievalLinkEvent')->save($event);
    }
}
