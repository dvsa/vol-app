<?php
namespace Permits\Controller;

use Zend\Http\Response as HttpResponse;
use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\View\Helper\EcmtSection;
use Dvsa\Olcs\Transfer\Command\Permits\AcceptEcmtPermits;

class FeePartSuccessfulController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP_FOR_ACCEPT_OR_DECLINE,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_AWAITING_FEE,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_ACCEPT_AND_PAY,
    ];

    protected $templateConfig = [
        'default' => 'permits/fee-part-successful',
    ];

    protected $templateVarsConfig = [
        'default' => [
            'browserTitle' => 'permits.page.fee.browser.title'
        ]
    ];

    protected $postConfig = [
        'default' => [
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_ECMT_PAYMENT_ACTION,
            'conditional' => [
                'command' => AcceptEcmtPermits::class,
                'dataKey' => 'application',
                'params' => 'id',
                'step' => EcmtSection::ROUTE_ISSUE_SUBMITTED,
                'field' => 'hasOutstandingFees',
                'value' => 0
            ]
        ],
    ];

    public function handlePost()
    {
        if (isset($this->postParams['Submit']['DeclineButton'])) {
            return $this->nextStep(EcmtSection::ROUTE_DECLINE_APPLICATION);
        }

        return parent::handlePost();
    }

    /**
     * @param \Common\Form\Form $form
     * @return \Common\Form\Form
     */
    public function alterForm($form)
    {
        if ($this->data['application']['hasOutstandingFees'] === 0) {
            $form->get('Submit')->get('SubmitButton')->setLabel('Accept');
        }

        return $form;
    }
}
