<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * LicencePsvDiscCountNotCeased
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicencePsvDiscCountNotCeased extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    public function handleQuery(QueryInterface $query)
    {
        try {
            /* @var $entity \Dvsa\Olcs\Api\Entity\Licence\Licence */
            $entity = $this->getRepo()->fetchUsingId($query);
        } catch (NotFoundException) {
            return null;
        }

        return $this->result(
            $entity,
            [],
            [
                'notCeasedPsvDiscCount' => $entity->getPsvDiscsNotCeasedCount()
            ]
        )->serialize();
    }
}
