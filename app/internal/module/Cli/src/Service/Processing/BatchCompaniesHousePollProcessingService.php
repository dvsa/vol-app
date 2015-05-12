<?php

/**
 * Batch process Companies House polling
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Cli\Service\Processing;

use Common\Service\Entity\LicenceStatusRuleEntityService;
use Common\Service\Entity\LicenceEntityService;
use Common\Util\RestCallTrait;
use Zend\Log\Logger;

/**
 * Batch process Companies House polling
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class BatchCompaniesHousePollProcessingService extends AbstractBatchProcessingService
{
    use RestCallTrait;

    private $start;

    private $api;

    private function startTimer()
    {
        $this->start = microtime(true);
    }

    private function getElapsedTime()
    {
        $now = microtime(true);
        return sprintf('%.6f', ($now - $this->start));
    }

    /**
     * Prepends elapsed time to any console output
     */
    protected function outputLine($text)
    {
        return parent::outputLine($this->getElapsedTime().': '.$text);
    }

    /**
     * Process stuff
     *
     * @return void
     */
    public function process()
    {
        $this->startTimer();

        $this->outputLine('START');

        $this->api = $this->getServiceLocator()->get('CompaniesHouseApi');

        $count = 0;

        $return = self::EXIT_CODE_SUCCESS;

        // list of company reg. no.s.
        $companies = [
            '06358941',
            '06358942',
            '06358943',
            '06358944',
            '06358945',
            '06358946',
            '06358947',
            '06358948',
            '06358949',
            '06358950',
            '06358951',
            '06358952',
        ];

        // $companies = ['06359089'];
        // foreach ($companies as $companyNo) {
        //     $result = $this->getCompanyProfileData($companyNo);
        //     $data = $this->normaliseProfileData($result);
        //     $this->storeCompanyData($data);
        //     $count ++;
        // }

        try {
            $start = '06358948';
            $end = '06359948';
            for ($i = $start; $i<$end; $i++) {
                $companyNo = str_pad($i, 8, '0', STR_PAD_LEFT);
                $result = $this->getCompanyProfileData($companyNo);
                $data = $this->normaliseProfileData($result);
                $this->storeCompanyData($data);
                $count ++;
            }
        } catch (\Zend\Http\Exception $e) {
            $this->outputLine('ERROR: '.$e->getMessage());
            $return = self::EXIT_CODE_ERROR;
        }

        $this->outputLine('Processed '.$count.' companies');
        $this->outputLine('Done');

        return $return;
    }

    /**
     * @param string $companyNumber
     * @return array
     * @see https://developer.companieshouse.gov.uk/api/docs/company/company_number/companyProfile-resource.html
     */
    protected function getCompanyProfileData($companyNumber)
    {
        $this->outputLine('Requesting data for company ['.$companyNumber.']');
        return $this->api->getCompanyProfile($companyNumber);
    }

    protected function normaliseProfileData($data)
    {
        $companyDetails = [
            'companyName' => $data['company_name'],
            'companyNumber' => $data['company_number'],
        ];

        $addressDetails = $this->getAddressDetails($data);

        $people = ['people' => $this->getOfficers($data)];

        return array_merge($companyDetails, $addressDetails, $people);
    }

    protected function storeCompanyData($data)
    {
        // @TODO
        $this->outputLine(json_encode($data));
    }

    /**
     * @param array $data
     * @return array
     * @see https://developer.companieshouse.gov.uk/api/docs/company/company_number/registered-office-address/registeredOfficeAddress-resource.html
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
            if (isset($data['registered_office_address'][$field])) {
                $addressDetails[$field] = $data['registered_office_address'][$field];
            }
        }

        return $addressDetails;
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

        return array_filter(
            $data['officer_summary']['officers'],
            function($officer) use ($roles) {
                return in_array($officer['officer_role'], $roles);
            }
        );
    }
}
