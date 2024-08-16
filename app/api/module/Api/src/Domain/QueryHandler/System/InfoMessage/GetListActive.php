<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\System\InfoMessage;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Handler for GET LIST of Active system info messages
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class GetListActive extends AbstractQueryHandler
{
    protected $repoServiceName = 'SystemInfoMessage';

    /**
     * @param \Dvsa\Olcs\Transfer\Query\System\InfoMessage\GetListActive $query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\SystemInfoMessage $repo */
        $repo = $this->getRepo();

        $result = $repo->fetchListActive($query);

        return [
            'result' => $result,    //  already array, not need to be serialized; secured from recursion
            'count' => count($result),
        ];
    }
}
