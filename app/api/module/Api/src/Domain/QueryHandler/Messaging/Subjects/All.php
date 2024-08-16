<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Subjects;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\MessagingSubject as SubjectRepo;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class All extends AbstractQueryHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::MESSAGING];
    protected $extraRepos = [SubjectRepo::class];

    public function handleQuery(QueryInterface $query): array
    {
        $subjects = $this->getSubjectRepository()->fetchList($query, Query::HYDRATE_OBJECT);

        return [
            'result' => $this->resultList($subjects),
            'count' => count($subjects),
        ];
    }

    private function getSubjectRepository(): SubjectRepo
    {
        return $this->getRepo(SubjectRepo::class);
    }
}
