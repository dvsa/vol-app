<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Query\Application\Documents as DocumentsQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class Documents extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    /**
     * Handle query
     *
     * @param QueryInterface|DocumentsQry $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /**
         * @var ApplicationRepo $repo
         * @var ApplicationEntity $application
         */
        $repo = $this->getRepo();
        $application = $this->getRepo()->fetchUsingId($query);

        return $this->resultList(
            $application->getApplicationDocuments(
                $repo->getCategoryReference($query->getCategory()),
                $repo->getSubCategoryReference($query->getSubCategory())
            )
        );
    }
}
