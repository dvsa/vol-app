<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Command\CompaniesHouse\CreateAlert as CreateAlertCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\NotReadyException;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseAlert as AlertEntity;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany as CompanyEntity;
use Dvsa\Olcs\CompaniesHouse\Service\Exception\NotFoundException as ChNotFoundException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\I18n\Validator\Alnum;
use Olcs\Logging\Log\Logger;

/**
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class Compare extends AbstractCommandHandler
{
    protected $extraRepos = ['CompaniesHouseAlert', 'Organisation'];

    /**
     * Given a company number, looks up data via Companies House API and
     * checks for differences with last-stored data
     *
     * @param \Dvsa\Olcs\Api\Domain\Command\CompaniesHouse\Compare $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->result->setFlag('isInsolvent', false);

        $companyNumber = $command->getCompanyNumber();

        try {
            if (!$this->validateCompanyNumber($companyNumber)) {
                throw new ChNotFoundException('Company number has invalid characters');
            }
            $apiResult = $this->api->getCompanyProfile($companyNumber, true);
        } catch (ChNotFoundException) {
            $this->result->merge(
                $this->createAlert(
                    [AlertEntity::REASON_INVALID_COMPANY_NUMBER],
                    $companyNumber
                )
            );
            return $this->result;
        } catch (\Exception $e) {
            $exception = new NotReadyException(
                'Error getting data from Companies House API : ' . $e->getMessage(),
                0,
                $e
            );
            $exception->setRetryAfter(60);
            throw $exception;
        }

        $data = $this->normaliseProfileData($apiResult);

        try {
            $stored = $this->getRepo()->getLatestByCompanyNumber($companyNumber);
        } catch (NotFoundException) {
            // Company not previously stored, save new data and return
            $company = new CompanyEntity($data);
            $this->getRepo()->save($company);

            return $this->result
                ->addId('companiesHouseCompany', $company->getId())
                ->addMessage('Saved new company');
        }

        $reasons = $this->compare($stored->toArray(), $data);

        if (empty($reasons)) {
            // return early if no changes detected
            return $this->result->addMessage('No changes');
        }

        $this->result->merge($this->createAlert($reasons, $companyNumber));

        $company = new CompanyEntity($data);
        $this->getRepo()->save($company);

        return $this->result
            ->addId('companiesHouseCompany', $company->getId())
            ->addMessage('Saved company');
    }

    /**
     * Compare
     *
     * @param array $old stored company data
     * @param array $new new company data
     *
     * @return array - list of reason codes, empty if no changes
     */
    protected function compare($old, $new)
    {
        $changes = [];

        if ($this->statusHasChanged($old, $new)) {
            $isInsolvent = $this->isInsolvent($new);
            $this->result->setFlag('isInsolvent', $isInsolvent);
            $changes[] = AlertEntity::REASON_STATUS_CHANGE;

            if ($isInsolvent) {
                $logData = [
                    'oldStatus' => $old['companyStatus'],
                    'newStatus' => $new['companyStatus'],
                    'oldData' => $old,
                    'newData' => $old
                ];

                Logger::info('insolvent company found', $logData);
            }
        }

        if ($this->nameHasChanged($old, $new)) {
            $changes[] = AlertEntity::REASON_NAME_CHANGE;
        }

        if ($this->addressHasChanged($old, $new)) {
            $changes[] = AlertEntity::REASON_ADDRESS_CHANGE;
        }

        if ($this->peopleHaveChanged($old, $new)) {
            $changes[] = AlertEntity::REASON_PEOPLE_CHANGE;
        }

        return $changes;
    }

    /**
     * Create Alert
     *
     * @param array  $reasons       Reasons
     * @param string $companyNumber Company Nr
     *
     * @return Result
     */
    protected function createAlert($reasons, $companyNumber)
    {
        $command = CreateAlertCmd::create(
            [
                'companyNumber' => $companyNumber,
                'reasons' => $reasons,
            ]
        );
        return $this->handleSideEffect($command);
    }

    // comparison functions....

    /**
     * Status Has Changed
     *
     * @param array $old stored company data
     * @param array $new new company data
     *
     * @return boolean
     */
    protected function statusHasChanged($old, $new)
    {
        return ($new['companyStatus'] !== $old['companyStatus']);
    }

    /**
     * Name Has Changed
     *
     * @param array $old stored company data
     * @param array $new new company data
     *
     * @return boolean
     */
    protected function nameHasChanged($old, $new)
    {
        return (trim(strtolower((string) $new['companyName'])) !== trim(strtolower((string) $old['companyName'])));
    }

    /**
     * Address Has Changed
     *
     * @param array $old stored company data
     * @param array $new new company data
     *
     * @return boolean
     */
    protected function addressHasChanged($old, $new)
    {
        $fields = [
            "addressLine1",
            "addressLine2",
            "locality",
            "poBox",
            "postalCode",
            "premises",
            "region",
        ];

        foreach ($fields as $field) {
            // check for changes to fields we already have
            if (!is_null($old[$field])) {
                if (!isset($new[$field])) {
                    // field has been deleted
                    return true;
                }
                if (trim(strtolower((string) $new[$field])) !== trim(strtolower((string) $old[$field]))) {
                    // field has changed!
                    return true;
                }
            }

            // check for new fields that have been added
            if (isset($new[$field]) && trim(strtolower((string) $new[$field])) !== trim(strtolower((string) $old[$field]))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Array comparison of officer data
     *
     * @param array $old stored company data
     * @param array $new new company data
     *
     * @return bool
     */
    protected function peopleHaveChanged($old, $new)
    {
        $old = $this->getNormalisedPeople($old);
        $new = $this->getNormalisedPeople($new);

        if (count($old) !== count($new)) {
            return true;
        }

        foreach ($old as $key => $officer) {
            if ($new[$key] != $officer) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Normalised People
     *
     * @param array $data Officers data
     *
     * @return array
     */
    protected function getNormalisedPeople($data)
    {
        return array_map(
            function ($officer) {
                $normalised = [
                    'name' => $officer['name'],
                    'role' => $officer['role'],
                ];
                if (isset($officer['dateOfBirth'])) {
                    $dob = $officer['dateOfBirth'];
                    if (is_array($dob)) {
                        $dob = sprintf(
                            '%s-%02d-%02d',
                            $dob['year'],
                            $dob['month'],
                            $dob['day'] ?? 1
                        );
                    } elseif (is_object($dob)) {
                        $dob = $dob->format('Y-m-d');
                    }
                    $normalised['dateOfBirth'] = $dob;
                }
                return $normalised;
            },
            $data['officers']
        );
    }

    /**
     * Validate the company number
     * This is a simple check that the company number is only made up of alphanumerical characters
     *
     * @param string $companyNumber Company number
     *
     * @return bool
     *
     */
    private function validateCompanyNumber($companyNumber)
    {
        $validator = new Alnum();
        return $validator->isValid($companyNumber);
    }

    private function isInsolvent(array $data): bool
    {
        $insolvencyStatuses = [
            'administration',
            'liquidation',
            'receivership',
        ];
        return in_array($data['companyStatus'], $insolvencyStatuses);
    }
}
