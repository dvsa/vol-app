<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler as DomainAbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\CompaniesHouse\Service\Client as CompaniesHouseClient;
use Psr\Container\ContainerInterface;

/**
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractCommandHandler extends DomainAbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'CompaniesHouseCompany';

    /**
     * @var CompaniesHouseClient
     */
    protected $api;

    /**
     * @var \Laminas\Filter\Word\UnderscoreToCamelCase
     */
    protected $wordFilter;

    /**
     * Normalise Profile Data
     *
     * @param array $data Profile Data
     *
     * @return array
     */
    protected function normaliseProfileData($data)
    {
        $companyDetails = [
            'companyName' => $data['company_name'],
            'companyNumber' => strtoupper((string) $data['company_number']),
            'companyStatus' => $data['company_status'],
        ];

        $addressDetails = $this->getAddressDetails($data);

        $people = ['officers' => $this->getOfficers($data)];

        return array_merge($companyDetails, $addressDetails, $people);
    }

    /**
     * Normalize Address Data
     *
     * @param array $data Address Data
     *
     * @return array
     * @see https://developer.companieshouse.gov.uk/api/docs/company/company_number/
     * registered-office-address/registeredOfficeAddress-resource.html
     */
    protected function getAddressDetails($data)
    {
        $addressDetails = [];
        $addressFields = [
            'address_line_1',
            'address_line_2',
            'country',
            'locality',
            'po_box',
            'postal_code',
            'premises',
            'region',
        ];

        foreach ($addressFields as $field) {
            $newField = $this->normaliseFieldName($field);
            if (isset($data['registered_office_address'][$field])) {
                $addressDetails[$newField] = $data['registered_office_address'][$field];
            }
        }

        return $addressDetails;
    }

    /**
     * Normalize Field Name
     *
     * @param string $fieldName Field Name
     *
     * @return string
     */
    protected function normaliseFieldName($fieldName)
    {
        $newFieldName = lcfirst((string) $this->wordFilter->filter($fieldName));
        return str_replace('_', '', $newFieldName);
    }

    /**
     * Filter officers
     *
     * @param array $data Company data
     *
     * @return array
     */
    protected function getOfficers($data)
    {
        if (!isset($data['officer_summary']['officers']) || !is_array($data['officer_summary']['officers'])) {
            return [];
        }

        // filter officers to the roles we're interested in
        $roles = [
            'corporate-director',
            'director',
            'nominee-director',
            'corporate-nominee-director',
            'corporate-llp-member',
            'llp-member',
            'corporate-llp-designated-member',
            'llp-designated-member',
            'general-partner-in-a-limited-partnership',
            'limited-partner-in-a-limited-partnership',
            'receiver-and-manager',
            'judicial-factor',
        ];

        $officers = [];

        foreach ($data['officer_summary']['officers'] as $officer) {
            if (in_array($officer['officer_role'], $roles)) {
                $officerData =  [
                    'name' => $officer['name'],
                    'role' => $officer['officer_role'],
                ];
                if (isset($officer['date_of_birth'])) {
                    $officerData['dateOfBirth'] = $officer['date_of_birth'];
                }
                $officers[] = $officerData;
            }
        }
        return $officers;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->api = $container->get(CompaniesHouseClient::class);
        $this->wordFilter = new \Laminas\Filter\Word\UnderscoreToCamelCase();
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
