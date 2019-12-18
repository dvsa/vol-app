<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Create;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\LicencesAvailable;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\View\Helper\EcmtSection;
use Permits\View\Helper\IrhpApplicationSection;

class LicenceController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'add' => DataSourceConfig::PERMIT_APP_ADD_LICENCE,
        'question' => DataSourceConfig::PERMIT_APP_CHANGE_LICENCE,
    ];

    protected $conditionalDisplayConfig = [
        'add' => ConditionalDisplayConfig::PERMIT_APP_CAN_APPLY_LICENCE,
        'question' => ConditionalDisplayConfig::PERMIT_APP_CAN_APPLY_LICENCE_EXISTING_APP,
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
            'backUri' => IrhpApplicationSection::ROUTE_TYPE
        ],
        'question' => [
            'browserTitle' => 'permits.page.licence.browser.title',
            'question' => 'permits.page.licence.question',
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW
        ],
    ];

    protected $postConfig = [
        'add' => [
            'command' => Create::class,
            'params' => ParamsConfig::NEW_APPLICATION,
            'step' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ],
        'question' => [
            'params' => ParamsConfig::CONFIRM_CHANGE,
            'step' => IrhpApplicationSection::ROUTE_LICENCE_CONFIRM_CHANGE,
            'conditional' => [
                'dataKey' => 'licencesAvailable',
                'field' => 'selectedLicence',
                'compareParam' => 'licence',
                'step' => [
                    'route' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
                ],
                'saveAndReturnStep' => [
                    'route' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
                ],
            ]
        ],
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
        $licenceData = $this->data[LicencesAvailable::DATA_KEY]['eligibleLicences'][$params['licence']];

        if (isset($licenceData['activeApplicationId'])) {
            $config = $this->handleActiveApplicationResponse(
                $config,
                [
                    'id' => $licenceData['activeApplicationId'],
                    'licence' => $params['licence'],
                ],
                IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
                IrhpApplicationSection::ROUTE_ADD_LICENCE,
                IrhpApplicationSection::ROUTE_LICENCE
            );

            return;
        }

        if (isset($config['command'])) {
            //quick fix for mismatched route params
            $params['irhpPermitType'] = $params['type'];
            $params['irhpPermitStock'] = $this->routeParams['stock'] ?? null;

            /** @var CommandInterface $command */
            $command = $config['command']::create($params);

            $response = $this->handleCommand($command);
            $responseDump = $this->handleResponse($response);

            if ($config['params'] === ParamsConfig::NEW_APPLICATION) {
                $this->redirectParams = ['id' => $responseDump['id']['irhpApplication']];
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

    /**
     * Common code to handle response/config after Active Application query.
     *
     * @param array $config
     * @param array $activeApplication
     * @param string $overviewRoute
     * @param string $addRoute
     * @param string $changeRoute
     * @return array
     */
    protected function handleActiveApplicationResponse(array $config, array $activeApplication, string $overviewRoute, string $addRoute, string $changeRoute)
    {
        if ($config['params'] === ParamsConfig::CONFIRM_CHANGE) {
            $config['step'] = $overviewRoute;
            unset($config['command']);
        } elseif (isset($this->queryParams['active']) && ($activeApplication['licence'] == $this->queryParams['active'])) {
            $config['step'] = $overviewRoute;
            $this->redirectParams = ['id' => $activeApplication['id']];
        } else {
            $config['step'] = isset($config['command']) ? $addRoute : $changeRoute;
            $this->redirectOptions = [
                'query' => ['active' => $activeApplication['licence']]
            ];
        }
        return $config;
    }
}
