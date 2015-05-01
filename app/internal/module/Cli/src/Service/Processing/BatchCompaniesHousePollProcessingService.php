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

        $service = $this->getServiceLocator()->get('Data\CompaniesHouse');

        $count = 0;
        foreach ($companies as $companyNo) {
            $result = $this->normaliseResponseData(
                $companyNo,
                $service->search('companyDetails', $companyNo),
                $service->search('currentCompanyOfficers', $companyNo)
            );

            // $this->compare($result);

            $this->outputLine(json_encode($result));
            $count ++;
        }

        $this->outputLine('Processed '.$count.' companies');
        $this->outputLine('Done');
    }

    protected function compare($result)
    {
        // get last version of CH data from $result['CompanyNumber']
    }

    protected function normaliseResponseData($companyNo, $companyData, $officerData)
    {
        $companyData = isset($companyData['Results'][0]) ? $companyData['Results'][0] : [];
        $officerData = isset($officerData['Results'][0]) ? $officerData['Results'][0] : [];

        // format address data
        $fields = ['addressLine1', 'addressLine2', 'addressLine3', 'addressLine4', 'addressLine5', 'addressLine6'];
        $data = array_pad($companyData['RegAddress']['AddressLine'], count($fields), '');
        $addressData = array_combine($fields, $data);

        $result = array_merge(
            [
                'companyNumber' => $companyNo,
                'companyName' => $companyData['CompanyName'],
                'status' => $companyData['CompanyStatus'],
                'officers' => $officerData,
            ],
            $addressData
        );

        ksort($result);

        return $result;
    }
}
