<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\CompaniesHouse\Service\Client as CompaniesHouseClient;
use Dvsa\Olcs\CompaniesHouse\Service\Exception\NotFoundException as CompanyNotFoundException;
use Dvsa\Olcs\CompaniesHouse\Service\Exception\ServiceException;
use Dvsa\Olcs\Transfer\Query\CompaniesHouse\ByNumber as Qry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ByNumber extends AbstractQueryHandler
{
    private const int MAX_COMPANY_NUMBER_LENGTH = 8;

    /**
     * @var CompaniesHouseClient
     */
    protected $companiesHouseApi;

    /**
     * @param QueryInterface $query
     *
     * @return array
     * @throws NotFoundException
     * @throws NotFoundException | \Exception
     */
    public function handleQuery(QueryInterface $query)
    {
        /**
         * @var Qry
         */
        $companyNumber = $this->formatCompanyNumber($query->getCompanyNumber());

        try {
            $companyProfile = $this->companiesHouseApi->getCompanyProfile($companyNumber);
        } catch (CompanyNotFoundException) {
            throw new NotFoundException();
        } catch (ServiceException $exception) {
            throw new \Exception($exception->getMessage(), 0, $exception);
        }
        return [
            'count' => 1,
            'result' => [$companyProfile]
        ];
    }

    private function formatCompanyNumber(string $companyNumber): string
    {
        if (!str_starts_with($companyNumber, '0') && strlen($companyNumber) < self::MAX_COMPANY_NUMBER_LENGTH) {
            $companyNumber = str_pad($companyNumber, self::MAX_COMPANY_NUMBER_LENGTH, "0", STR_PAD_LEFT);
        }
        return $companyNumber;
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ByNumber
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->companiesHouseApi = $container->get(CompaniesHouseClient::class);
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
