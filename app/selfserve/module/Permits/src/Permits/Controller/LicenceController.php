<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\IrhpPermitApplication\Create;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;

use Permits\View\Helper\EcmtSection;
use Permits\View\Helper\IrhpApplicationSection;

class LicenceController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'add' => DataSourceConfig::PERMIT_APP_ADD_LICENCE,
        'question' => DataSourceConfig::PERMIT_APP_LICENCE
    ];

    protected $conditionalDisplayConfig = [
        'add' => ConditionalDisplayConfig::PERMIT_APP_CAN_APPLY_SINGLE,
        'question' => ConditionalDisplayConfig::PERMIT_APP_NOT_SUBMITTED,
    ];

    protected $formConfig = [
        'add' => FormConfig::FORM_ADD_LICENCE,
        'question' => FormConfig::FORM_LICENCE,
    ];

    protected $templateConfig = [
        'default' => 'permits/single-question'
    ];

    protected $templateVarsConfig = [
        'add' => [
            'browserTitle' => 'permits.page.licence.browser.title',
            'question' => 'permits.page.licence.question',
            'backUri' => EcmtSection::ROUTE_TYPE
        ],
        'question' => [
            'browserTitle' => 'permits.page.licence.browser.title',
            'question' => 'permits.page.licence.question',
        ]
    ];

    protected $postConfig = [
        'add' => [
            'command' => Create::class,
            'params' => ParamsConfig::NEW_APPLICATION,
            'step' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ],
        'question' => [
            'params' => ParamsConfig::CONFIRM_CHANGE,
            'step' => EcmtSection::ROUTE_CONFIRM_CHANGE,
            'conditional' => [
                'dataKey' => 'application',
                'value' => 'licence',
                'step' => EcmtSection::ROUTE_APPLICATION_OVERVIEW,
                'field' => ['licence', 'id'],
            ]
        ]
    ];

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        return $this->genericAction();
    }

    /**
     * @param array $config
     * @param array $params
     */
    public function handlePostCommand(array &$config, array $params)
    {
        if (isset($config['command'])) {
            $command = $config['command']::create($params);
            $response = $this->handleCommand($command);
            $responseDump = $this->handleResponse($response);
            if ($config['params'] === ParamsConfig::NEW_APPLICATION) {
                if (isset($responseDump['id']['ecmtPermitApplication'])) {
                    $field = 'ecmtPermitApplication';
                    $config['step'] = EcmtSection::ROUTE_APPLICATION_OVERVIEW;
                } else {
                    $field = 'irhpApplication';
                }
                $this->redirectParams = ['id' => $responseDump['id'][$field]];
            }
        } else {
            if (isset($config['params'])) {
                if ($config['params'] === ParamsConfig::CONFIRM_CHANGE) {
                    $this->redirectParams = [
                        'licence' => $params['licence']
                    ];
                }
            }
        }
    }
}
