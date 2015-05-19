<?php

/**
 * Companies House Compare Business Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Cli\BusinessService\Service;

use Common\BusinessService\Response;
use Common\Service\Entity\CompaniesHouseAlertEntityService;
use Common\Exception\ResourceNotFoundException;

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
        $companyEntityService = $this->getServiceLocator()->get('Entity\CompaniesHouseCompany');

        try {

            $result = $this->getApi()->getCompanyProfile($params['companyNumber']);
            $data = $this->normaliseProfileData($result);
            if (empty($data['companyNumber'])) {
                $alert = $this->createAlert(
                    [CompaniesHouseAlertEntityService::REASON_INVALID_COMPANY_NUMBER],
                    $params['companyNumber']
                );
                return new Response(Response::TYPE_SUCCESS, ['alert' => $alert], 'Alert created');
            }

            $stored = $companyEntityService->getLatestByCompanyNumber($params['companyNumber']);

            if (!$stored) {
                // Company not previously stored, save new data and return
                $saved = $companyEntityService->saveNew($data);
                return new Response(
                    Response::TYPE_SUCCESS,
                    ['company' => $saved],
                    'Saved new company id '. $saved['id']
                );
            }

            $reasons = $this->compare($stored, $data);

            if (empty($reasons)) {
                // return early if no changes detected
                return new Response(Response::TYPE_NO_OP);
            }

            $alert = $this->createAlert($reasons, $params['companyNumber']);
            $saved = $companyEntityService->saveNew($data);

            return new Response(
                Response::TYPE_SUCCESS,
                ['company' => $saved, 'alert' => $alert],
                'Alert created'
            );

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

    /**
     * @param array $reasons
     * @param string $companyNumber
     */
    protected function createAlert($reasons, $companyNumber)
    {
        $alertData = [
            'companyOrLlpNo' => $companyNumber,
            'reasons' => [],
            'name' => null,
            'organisation' => null,
        ];

        foreach ($reasons as $reason) {
            $alertData['reasons'][]['reasonType'] = $reason;
        }

        $organisation = $this->getOrganisation($companyNumber);

        if (!$organisation) {
            throw new ResourceNotFoundException('No organisation found for company no. \''.$companyNumber.'\'');
        }

        $alertData['name'] = $organisation['name'];
        $alertData['organisation'] = $organisation['id'];

        return $this->getServiceLocator()->get('Entity\CompaniesHouseAlert')
            ->saveNew($alertData);
    }

    /**
     * @param string $companyNumber
     * @return array|false
     */
    protected function getOrganisation($companyNumber)
    {
        $results = $this->getServiceLocator()->get('Entity\Organisation')
            ->getByCompanyOrLlpNo($companyNumber);

        // @note returns the first matching organisation only
        return isset($results['Results'][0]) ? $results['Results'][0] : false;
    }

    // comparison functions....

    /**
     * @param array $old stored company data
     * @param array $new new company data
     * @return boolean
     */
    protected function statusHasChanged($old, $new)
    {
        return ($new['companyStatus'] !== $old['companyStatus']);
    }

    /**
     * @param array $old stored company data
     * @param array $new new company data
     * @return boolean
     */
    protected function nameHasChanged($old, $new)
    {
        return (trim(strtolower($new['companyName'])) !== trim(strtolower($old['companyName'])));
    }

    /**
     * @param array $old stored company data
     * @param array $new new company data
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
                if (trim(strtolower($new[$field])) !== trim(strtolower($old[$field]))) {
                    // field has changed!
                    return true;
                }
            }

            // check for new fields that have been added
            if (isset($new[$field]) && trim(strtolower($new[$field])) !== trim(strtolower($old[$field]))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Array comparison of officer data
     */
    protected function peopleHaveChanged($old, $new)
    {
        $old = $old['officers'];
        $new = $new['officers'];

        array_walk(
            $old,
            function (&$officer) {
                ksort($officer);
                return $officer;
            }
        );
        array_walk(
            $new,
            function (&$officer) {
                ksort($officer);
                return $officer;
            }
        );

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
}
