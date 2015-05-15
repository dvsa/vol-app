<?php

/**
 * Companies House Compare Business Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Cli\BusinessService\Service;

use Common\BusinessService\Response;
use Common\Service\Entity\CompaniesHouseAlertEntityService;

/**
 * Companies House Compare Business Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CompaniesHouseCompare extends CompaniesHouseAbstract
{
    /**
     * Given a company number, looks up data via Companies House API and
     * checks for differences with last-stored data
     */
    public function process(array $params)
    {
        try {

            $result = $this->getApi()->getCompanyProfile($params['companyNumber']);
            $data = $this->normaliseProfileData($result);
            if (empty($data['companyNumber'])) {
                return new Response(Response::TYPE_FAILED, [], 'Company not found');
            }

            $stored = $this->getServiceLocator()->get('Entity\CompaniesHouseCompany')
                ->getByCompanyNumberForCompare($params['companyNumber']);

            $reasons = $this->compare($stored, $data);

            if (empty($reasons)) {
                // return early if no changes detected
                return new Response(Response::TYPE_NO_OP);
            }

            // create alert (@TODO move to business service)
            $alertData = [
                'companyOrLlpNo' => $params['companyNumber'],
                'name' => $stored['companyName'],
                'organisation' => 1, // @TODO
                'reasons' => []
            ];
            foreach ($reasons as $reason) {
                $alertData['reasons'][]['reasonType'] = $reason;
            }
            $saved = $this->getServiceLocator()->get('Entity\CompaniesHouseAlert')
                ->saveNew($alertData);

            return new Response(Response::TYPE_SUCCESS, $saved, 'Alert created');

        } catch (\Exception $e) {
            return new Response(Response::TYPE_FAILED, [], $e->getMessage());
        }
    }

    /**
     * @param array $old stored company data
     * @param array $new new company data
     * @return array - list of reason codes, empty if no changes
     */
    protected function compare($old, $new)
    {
        $changes = [];

        if ($this->statusHasChanged($old, $new)) {
            $changes[] = CompaniesHouseAlertEntityService::REASON_STATUS_CHANGE;
        }

        if ($this->nameHasChanged($old, $new)) {
            $changes[] = CompaniesHouseAlertEntityService::REASON_NAME_CHANGE;
        }

        if ($this->addressHasChanged($old, $new)) {
            $changes[] = CompaniesHouseAlertEntityService::REASON_ADDRESS_CHANGE;
        }

        if ($this->peopleHaveChanged($old, $new)) {
            $changes[] = CompaniesHouseAlertEntityService::REASON_PEOPLE_CHANGE;
        }

        return $changes;
    }

    // comparison functions....
    // @TODO trim whitespace and ignore case?
    protected function statusHasChanged($old, $new)
    {
        return ($new['companyStatus'] !== $old['companyStatus']);
    }

    protected function nameHasChanged($old, $new)
    {
        return ($new['companyName'] !== $old['companyName']);
    }

    // @TODO
    protected function addressHasChanged($old, $new)
    {
         $fields = [
            "addressLine1",
            "addressLine2",
            // "companyName",
            // "companyNumber",
            "locality",
            "poBox",
            "postalCode",
            "premises",
            "region",
            // "id",
            // "version",
            // "company_status",
            // "officers",
        ];
        return false;
    }

    // @TODO
    protected function peopleHaveChanged($old, $new)
    {
        return false;
    }
}
