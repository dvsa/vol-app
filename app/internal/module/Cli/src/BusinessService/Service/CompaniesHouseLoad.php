<?php

/**
 * Companies House Load Business Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Cli\BusinessService\Service;

use Common\BusinessService\Response;

/**
 * Companies House Load Business Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CompaniesHouseLoad extends CompaniesHouseAbstract
{
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
}
