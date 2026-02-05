<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Fee;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Doctrine\ORM\Query as DoctrineQuery;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Fee
 */
class FeeList extends AbstractQueryHandler
{
    /**
     * @var \Dvsa\Olcs\Api\Service\FeesHelperService
     */
    protected $feesHelper;

    protected $repoServiceName = 'Fee';

    protected $extraRepos = ['Licence', 'Application'];

    public function handleQuery(QueryInterface $query)
    {
        /** @var FeeRepo $repo */
        $repo = $this->getRepo();

        $fees = $repo->fetchList($query, DoctrineQuery::HYDRATE_OBJECT);

        $data = $query->getArrayCopy();

        unset($data['status']);

        $unfilteredQuery = \Dvsa\Olcs\Transfer\Query\Fee\FeeList::create($data);

        return [
            'result' => $this->resultList($fees),
            'count' => $repo->fetchCount($query),
            'count-unfiltered' => $repo->hasRows($unfilteredQuery),
            'allowFeePayments' => $this->shouldAllowFeePayments($query),
            'minPayment' => $this->feesHelper->getMinPaymentForFees($fees->getArrayCopy()),
            'totalOutstanding' => $this->feesHelper->getTotalOutstanding($fees->getArrayCopy()),
        ];
    }

    private function shouldAllowFeePayments(QueryInterface $query)
    {
        if (!is_null($query->getLicence())) {
            $licence = $this->getRepo('Licence')->fetchById($query->getLicence());
            return $licence->allowFeePayments();
        }

        if (!is_null($query->getApplication())) {
            $application = $this->getRepo('Application')->fetchById($query->getApplication());
            return $application->allowFeePayments();
        }

        return true;
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return FeeList
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->feesHelper = $container->get('FeesHelperService');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
