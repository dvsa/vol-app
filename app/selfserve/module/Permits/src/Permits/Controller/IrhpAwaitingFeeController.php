<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\Permits\AcceptIrhpPermits;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\Data\Mapper\IrhpApplicationFeeSummary;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpAwaitingFeeController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_AWAITING_FEE,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_IS_AWAITING_FEE,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_ACCEPT_AND_PAY,
    ];

    protected $templateConfig = [
        'default' => 'permits/irhp-awaiting-fee',
    ];

    protected $templateVarsConfig = [
        'default' => [
            'prependTitleDataKey' => IrhpAppDataSource::DATA_KEY,
            'browserTitle' => 'permits.page.irhp.awaiting-fee.browser.title',
            'backUri' => IrhpApplicationSection::ROUTE_PERMITS,
        ]
    ];

    protected $postConfig = [
        'default' => [
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_PAYMENT_ACTION,
            'conditional' => [
                'dataKey' => 'application',
                'params' => 'id',
                'step' => [
                    'command' => AcceptIrhpPermits::class,
                    'route' => IrhpApplicationSection::ROUTE_SUBMITTED,
                ],
                'field' => 'hasOutstandingFees',
                'value' => false,
            ]
        ],
    ];

    public function handlePost()
    {
        if (isset($this->postParams['Submit']['DeclineButton'])) {
            return $this->nextStep(IrhpApplicationSection::ROUTE_DECLINE_APPLICATION);
        }

        return parent::handlePost();
    }

    /**
     * @param \Common\Form\Form $form
     *
     * @return \Common\Form\Form
     */
    public function alterForm($form)
    {
        if (!$this->data[IrhpAppDataSource::DATA_KEY]['hasOutstandingFees']) {
            $form->get('Submit')->get('SubmitButton')->setLabel('permits.page.accept');
        }

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveData()
    {
        parent::retrieveData();

        $this->data = $this->getServiceLocator()
            ->get(IrhpApplicationFeeSummary::class)
            ->mapForDisplay($this->data);
    }
}
