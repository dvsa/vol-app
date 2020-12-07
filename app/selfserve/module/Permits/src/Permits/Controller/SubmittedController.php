<?php
namespace Permits\Controller;

use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\View\Helper\IrhpApplicationSection;

class SubmittedController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP,
        'irhp-submitted' => DataSourceConfig::IRHP_APP,
    ];

    protected $conditionalDisplayConfig = [
        'issue-submitted' => ConditionalDisplayConfig::PERMIT_APP_ISSUING,
        'irhp-submitted' => ConditionalDisplayConfig::IRHP_APP_SUBMITTED,
    ];

    protected $templateConfig = [
        'default' => 'permits/confirmation',
    ];

    protected $templateVarsConfig = [
        'issue-submitted' => [
            'browserTitle' => 'permits.page.confirmation.issue-submitted.browser.title',
            'prependTitleDataKey' => IrhpAppDataSource::DATA_KEY,
            'title' => 'permits.page.confirmation.issue-submitted.title',
            'extraContent' => [
                'title' => 'permits.page.confirmation.bullet.list.title',
                'list' => 'markup-ecmt-issue-submitted-confirmation'
            ],
            'warning' => 'permits.page.confirmation.submitted.warning',
            'receiptUrl' => ''
        ],
        'irhp-submitted' => [
            'browserTitle' => 'permits.page.confirmation.irhp-submitted.browser.title',
            'prependTitleDataKey' => IrhpAppDataSource::DATA_KEY,
            'title' => 'permits.page.confirmation.irhp-submitted.title',
            'extraContent' => [
                'title' => 'permits.page.confirmation.irhp-submitted.bullet.list.title',
                'list' => 'markup-irhp-submitted-what-happens-next'
            ],
            'warning' => 'permits.page.confirmation.irhp-submitted.warning',
            'receiptUrl' => ''
        ],
    ];

    public function issueSubmittedAction()
    {
        $this->addReceiptUrl(IrhpApplicationSection::ROUTE_PRINT_RECEIPT);
        return parent::genericAction();
    }

    /**
     * IRHP submitted action
     *
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function irhpSubmittedAction()
    {
        $irhpAppData = $this->data[IrhpAppDataSource::DATA_KEY];

        if ($irhpAppData['isSubmittedForConsideration']) {
            // change content of the submitted page if the application is submitted for consideration
            $this->data['extraContent']['list']
                = 'markup-irhp-submitted-uc-what-happens-next-'.$irhpAppData['businessProcess']['id'];
        } elseif ($irhpAppData['irhpPermitType']['isEcmtShortTerm'] || $irhpAppData['irhpPermitType']['isEcmtAnnual']) {
            // Short term ECMT/Annual ECMT confirmation page after user pays issue fee successfully
            $this->data['browserTitle'] = 'permits.page.confirmation.irhp-payment-successful.browser.title';
            $this->data['title'] = 'permits.page.confirmation.irhp-payment-successful.title';
        } elseif ($irhpAppData['irhpPermitType']['isEcmtRemoval']) {
            $this->data['extraContent']['list'] = 'markup-irhp-submitted-ecmt-removal-what-happens-next';
        } elseif ($irhpAppData['irhpPermitType']['isCertificateOfRoadworthiness']) {
            $this->data['extraContent']['list'] = 'markup-irhp-submitted-certificate';
        } elseif ($irhpAppData['irhpPermitType']['isBilateral']) {
            $this->data['extraContent']['list'] = 'markup-irhp-submitted-bilateral-what-happens-next';
        }

        $this->addReceiptUrl(IrhpApplicationSection::ROUTE_PRINT_RECEIPT);
        return parent::genericAction();
    }

    /**
     * Get the receipt url
     *
     * @param string $route route for redirect
     *
     * @return void
     */
    private function addReceiptUrl(string $route): void
    {
        if (!empty($this->params()->fromQuery('receipt_reference'))) {
            $routeParams = [
                'id' => $this->params()->fromRoute('id'),
                'reference' => $this->params()->fromQuery('receipt_reference')
            ];

            $this->data['receiptUrl'] = $this->url()->fromRoute($route, $routeParams);
        }
    }
}
