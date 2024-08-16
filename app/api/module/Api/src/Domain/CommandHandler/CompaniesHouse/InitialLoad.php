<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\Exception as DomainException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany as CompanyEntity;
use Dvsa\Olcs\CompaniesHouse\Service\Exception\ServiceException as ApiException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Companies House Initial Load
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class InitialLoad extends AbstractCommandHandler
{
    /**
     * Command handler
     *
     * @param CommandInterface $command Command
     *
     * @return Result
     * @throws DomainException
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $companyNumber = $command->getCompanyNumber();
        $result = new Result();

        try {
            $apiResult = $this->api->getCompanyProfile($companyNumber, true);
        } catch (ApiException $e) {
            // rethrow client exception as domain exception
            throw new DomainException('Failure from Companies House API: ' . $e->getMessage(), 0, $e);
        }

        $data = $this->normaliseProfileData($apiResult);
        $company = new CompanyEntity($data);
        $this->getRepo()->save($company);

        $result
            ->addId('companiesHouseCompany', $company->getId())
            ->addMessage('Company added');

        return $result;
    }
}
