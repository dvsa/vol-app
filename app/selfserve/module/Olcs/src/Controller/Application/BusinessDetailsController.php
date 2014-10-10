<?php

/**
 * Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Section;
use Common\Controller\Traits\Lva\BusinessDetailsTrait;

/**
 * Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BusinessDetailsController extends AbstractApplicationController
{
    use BusinessDetailsTrait;

    /**
     * Business details section
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $orgId = $this->getCurrentOrganisationId();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $orgData = $this->getEntityService('Organisation')->getBusinessDetailsData($orgId);
            $data = $this->formatDataForForm($orgData);
        }

        $form = $this->getHelperService('FormHelper')
            ->createForm('Lva\BusinessDetails')
            ->setData($data);

        $tableData = $this->getEntityService('CompanySubsidiary')
            ->getAllForOrganisation($orgId);

        $table = $this->getServiceLocator()
            ->get('Table')
            ->buildTable(
                'application_your-business_business_details-subsidiaries',
                $tableData,
                array(), // params?
                false
            );

        $form->get('table')  // fieldset
            ->get('table')   // element
            ->setTable($table);

        if ($request->isPost() && $form->isValid()) {
            if (isset($data['tradingNames'])) {
                $tradingNames = $this->formatTradingNamesDataForSave($data);
                $this->getEntityService('TradingNames')->save($tradingNames);
            }

            $data = $this->formatDataForSave($data);
            $data['id'] = $orgId;
            $this->getEntityService('Organisation')->save($data);

            return $this->completeSection('business_details');
        }

        return new Section(
            array(
                'title' => 'Business details',
                'form' => $form
            )
        );
    }
    public function formatTradingNamesDataForSave($data)
    {
        $tradingNames = [];

        foreach ($data['tradingNames']['trading_name'] as $tradingName) {
            if (trim($tradingName['text']) !== '') {
                $tradingNames[] = [
                    'name' => $tradingName['text']
                ];
            }
        }

        $data['tradingNames'] = $tradingNames;

        return array(
            'organisation' => $data['id'],
            'licence' => $licence['id'],
            'tradingNames' => $tradingNames
        );
    }
}
