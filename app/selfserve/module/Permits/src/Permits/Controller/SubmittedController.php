<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\View\Helper\EcmtSection;

class SubmittedController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP,
    ];

    protected $conditionalDisplayConfig = [
        'application-submitted' => ConditionalDisplayConfig::PERMIT_APP_UNDER_CONSIDERATION,
        'issue-submitted' => ConditionalDisplayConfig::PERMIT_APP_PAID,
    ];

    protected $templateConfig = [
        'default' => 'permits/confirmation',
    ];

    protected $templateVarsConfig = [
        'application-submitted' => [
            'browserTitle' => 'permits.page.confirmation.application-submitted.browser.title',
            'title' => 'permits.page.confirmation.application-submitted.title',
            'extraContent' => [
                'title' => 'permits.page.confirmation.bullet.list.title',
                'list' => 'en_GB/bullets/markup-ecmt-application-submitted-confirmation'
            ],
            'warning' => 'permits.page.confirmation.submitted.warning',
            'receiptUrl' => ''
        ],
        'issue-submitted' => [
            'browserTitle' => 'permits.page.confirmation.issue-submitted.browser.title',
            'title' => 'permits.page.confirmation.issue-submitted.title',
            'extraContent' => [
                'title' => 'permits.page.confirmation.bullet.list.title',
                'list' => 'en_GB/bullets/markup-ecmt-issue-submitted-confirmation'
            ],
            'warning' => 'permits.page.confirmation.submitted.warning',
            'receiptUrl' => ''
        ],
    ];

    public function applicationSubmittedAction()
    {
        if (!empty($this->params()->fromQuery('receipt_reference'))) {
            $this->data['receiptUrl'] = $this->url()->fromRoute(EcmtSection::ROUTE_PRINT_RECEIPT, ['id' => $this->params()->fromRoute('id'), 'reference' => $this->params()->fromQuery('receipt_reference')]);
        }

        return parent::genericAction();
    }

    public function issueSubmittedAction()
    {
        if (!empty($this->params()->fromQuery('receipt_reference'))) {
            $this->data['receiptUrl'] = $this->url()->fromRoute(EcmtSection::ROUTE_PRINT_RECEIPT, ['id' => $this->params()->fromRoute('id'), 'reference' => $this->params()->fromQuery('receipt_reference')]);
        }

        return parent::genericAction();
    }
}
