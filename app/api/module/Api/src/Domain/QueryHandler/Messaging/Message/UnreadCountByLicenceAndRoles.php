<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Message;

use Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Conversations\AbstractConversationQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\Messaging\Messages\UnreadCountByLicenceAndRoles as UnreadCountByLicenceAndRolesQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class UnreadCountByLicenceAndRoles extends AbstractConversationQueryHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::MESSAGING];
    protected $extraRepos = [Repository\Message::class];

    /** @param UnreadCountByLicenceAndRolesQuery | QueryInterface $query */
    public function handleQuery(QueryInterface $query): array
    {
        $messageRepository = $this->getRepo(Repository\Message::class);
        $results = $messageRepository->getUnreadConversationCountByLicenceAndRoles(
            (int)$query->getLicence(),
            $query->getRoles(),
        );

        return ['count' => $results];
    }
}
