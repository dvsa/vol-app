<?php

/**
 * Companies House Load
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Cli\BusinessService\Service;

use Common\BusinessService\BusinessServiceInterface;
use Common\BusinessService\Response;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Companies House Load
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CompaniesHouseLoad implements BusinessServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @var Common\Service\CompaniesHouse\Api
     */
    private $api;

    /**
     * @var Zend\Filter\Word\UnderscoreToCamelCase
     */
    private $filter;

    /**
     * Given a company number, looks up data via Companies House API and
     * inserts a record in the db
     */
    public function process(array $params)
    {
        try {
            $result = $this->getApi()->getCompanyProfile($params['companyNumber']);
            $data = $this->normaliseProfileData($result);
            if (empty($data['companyNumber'])) {
                return new Response(Response::TYPE_FAILED, [], 'Company not found');
            }
            $saved = $this->getServiceLocator()->get('Entity\CompaniesHouseCompany')
                ->saveNew($data);
        } catch (\Exception $e) {
            return new Response(Response::TYPE_FAILED, [], $e->getMessage());
        }

        return new Response(Response::TYPE_SUCCESS, $saved, 'Saved company id '. $saved['id']);
    }

    protected function getApi()
    {
        if (is_null($this->api)) {
            $this->api = $this->getServiceLocator()->get('CompaniesHouseApi');
        }
        return $this->api;
    }

    protected function getFilter()
    {
        if (is_null($this->filter)) {
            $this->filter = new \Zend\Filter\Word\UnderscoreToCamelCase();
        }
        return $this->filter;
    }

    protected function normaliseProfileData($data)
    {
        $companyDetails = [
            'companyName' => $data['company_name'],
            'companyNumber' => $data['company_number'],
            'companyStatus' => $data['company_status'],
        ];

        $addressDetails = $this->getAddressDetails($data);

        $people = ['officers' => $this->getOfficers($data)];

        return array_merge($companyDetails, $addressDetails, $people);
    }

    /**
     * @param array $data
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

    protected function normaliseFieldName($fieldName)
    {
        $newFieldName = lcfirst($this->getFilter()->filter($fieldName));
        return str_replace('_', '', $newFieldName);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getOfficers($data)
    {
        if (!is_array($data['officer_summary']['officers'])) {
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
                    'role' => substr($officer['officer_role'], 0, 32), // roles are refData with max length of 32
                ];
                if (isset($officer['date_of_birth'])) {
                    $officerData['dateOfBirth'] = $officer['date_of_birth'];
                }
                $officers[] = $officerData;
            }
        }
        return $officers;
    }
}
