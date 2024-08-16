<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Conversations;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CaseRepo;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByCaseToLicence as GetConversationsByCaseToLicenceQuery;
use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByLicence as GetConversationsByLicenceQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class ByCaseToLicence extends AbstractQueryHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::MESSAGING];
    protected $extraRepos = [CaseRepo::class];

    /** @param GetConversationsByCaseToLicenceQuery|QueryInterface $query */
    public function handleQuery(QueryInterface $query)
    {
        $caseRepo = $this->getRepo(CaseRepo::class);
        /** @var Cases $case */
        $case = $caseRepo->fetchById($query->getCase());

        $licenceQuery = [
            'page'     => $query->getPage(),
            'limit'    => $query->getLimit(),
            'licence'  => $case->getLicence()->getId(),
            'statuses' => $query->getStatuses(),
        ];
        return $this->getQueryHandler()->handleQuery(GetConversationsByLicenceQuery::create($licenceQuery));
    }
}
