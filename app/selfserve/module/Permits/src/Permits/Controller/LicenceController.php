<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\RefData;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Create;
use Dvsa\Olcs\Transfer\Command\Permits\CreateEcmtPermitApplication;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ActiveApplication;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\PermitsAvailable;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\PermitsAvailableByYear;
use Dvsa\Olcs\Transfer\Query\Permits\ActiveEcmtApplication;
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
        'question' => DataSourceConfig::PERMIT_APP_LICENCE,
        'question-ecmt' => DataSourceConfig::PERMIT_APP_ECMT_LICENCE,
    ];

    protected $conditionalDisplayConfig = [
        'add' => ConditionalDisplayConfig::PERMIT_APP_CAN_APPLY_SINGLE,
        'question' => ConditionalDisplayConfig::IRHP_APP_NOT_SUBMITTED,
        'question-ecmt' => ConditionalDisplayConfig::PERMIT_APP_NOT_SUBMITTED,
    ];

    protected $formConfig = [
        'add' => FormConfig::FORM_ADD_LICENCE,
        'question' => FormConfig::FORM_LICENCE,
        'question-ecmt' => FormConfig::FORM_ECMT_LICENCE,
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
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW
        ],
        'question-ecmt' => [
            'browserTitle' => 'permits.page.licence.browser.title',
            'question' => 'permits.page.licence.question',
            'backUri' => EcmtSection::ROUTE_APPLICATION_OVERVIEW
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
                'dataKey' => 'application',
                'value' => 'licence',
                'step' => [
                    'route' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
                ],
                'saveAndReturnStep' => [
                    'route' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
                ],
                'field' => ['licence', 'id'],
            ]
        ],
        'question-ecmt' => [
            'params' => ParamsConfig::CONFIRM_CHANGE,
            'step' => EcmtSection::ROUTE_CONFIRM_CHANGE,
            'conditional' => [
                'dataKey' => 'application',
                'value' => 'licence',
                'step' => [
                    'route' => EcmtSection::ROUTE_APPLICATION_OVERVIEW,
                ],
                'saveAndReturnStep' => [
                    'route' => EcmtSection::ROUTE_APPLICATION_OVERVIEW,
                ],
                'field' => ['licence', 'id'],
            ]
        ],
    ];

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $this->templateVarsConfig['add']['question'] = $this->data['question'];
        $this->templateVarsConfig['add']['questionArgs'] = $this->data['questionArgs'];
        if (!empty($this->params()->fromRoute('year'))) {
            $this->templateVarsConfig['add']['backUri'] = IrhpApplicationSection::ROUTE_YEAR;
        }
        $this->mergeTemplateVars();

        return $this->genericAction();
    }

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function questionEcmtAction()
    {
        $this->templateVarsConfig['add']['question'] = $this->data['question'];
        $this->templateVarsConfig['add']['questionArgs'] = $this->data['questionArgs'];
        $this->mergeTemplateVars();
        return $this->genericAction();
    }

    /**
     * @param array $config
     * @param array $params
     */
    public function handlePostCommand(array &$config, array $params)
    {
        $irhpPermitTypeID = RefData::ECMT_PERMIT_TYPE_ID;

        if (isset($this->data['application']['irhpPermitType']['id'])) {
            $irhpPermitTypeID = $this->data['application']['irhpPermitType']['id'];
        } elseif (isset($this->data['irhpPermitType']['id'])) {
            $irhpPermitTypeID = $this->data['irhpPermitType']['id'];
        }

        if ($irhpPermitTypeID == RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID) {
            if (isset($this->routeParams['id'])) {
                $irhpApplicationId = $this->routeParams['id'];
                $permitsAvailableCommand = PermitsAvailable::create(['id' => $irhpApplicationId]);
            } else {
                $year = $params['year'];
                $permitsAvailableCommand = PermitsAvailableByYear::create(['year' => $year]);
            }

            $permitsAvailable = $this->handleResponse(
                $this->handleQuery($permitsAvailableCommand)
            );

            if (!$permitsAvailable['permitsAvailable']) {
                $config['step'] = IrhpApplicationSection::ROUTE_WINDOW_CLOSED;
                return;
            }
        }

        if ($irhpPermitTypeID == RefData::ECMT_PERMIT_TYPE_ID) {
            $activeApplication = $this->handleResponse(
                $this->handleQuery(
                    ActiveEcmtApplication::create(
                        [
                            'licence' => $params['licence'],
                            'year' => $this->params()->fromRoute('year'),
                            'id' => $this->params()->fromRoute('id'),
                        ]
                    )
                )
            );

            if (isset($activeApplication['id'])) {
                $config = $this->handleActiveApplicationResponse(
                    $config,
                    $activeApplication,
                    EcmtSection::ROUTE_APPLICATION_OVERVIEW,
                    EcmtSection::ROUTE_ADD_LICENCE,
                    EcmtSection::ROUTE_LICENCE
                );
                return;
            }
        } else {
            $activeApplicationParams = [
                'licence' => $params['licence'],
                'irhpPermitType' => $irhpPermitTypeID
            ];

            if ($irhpPermitTypeID == RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID) {
                $activeApplicationParams['year'] = $params['year'];
            }

            $activeApplication = $this->handleResponse(
                $this->handleQuery(
                    ActiveApplication::create($activeApplicationParams)
                )
            );

            if (isset($activeApplication['id'])) {
                $config = $this->handleActiveApplicationResponse(
                    $config,
                    $activeApplication,
                    IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
                    IrhpApplicationSection::ROUTE_ADD_LICENCE,
                    IrhpApplicationSection::ROUTE_LICENCE
                );
                return;
            }
        }

        if (isset($config['command'])) {
            if ($irhpPermitTypeID === RefData::ECMT_PERMIT_TYPE_ID) {
                $config['command'] = CreateEcmtPermitApplication::class;
            }

            $command = $config['command']::create($params);

            $response = $this->handleCommand($command);
            $responseDump = $this->handleResponse($response);

            if ($config['params'] === ParamsConfig::NEW_APPLICATION) {
                $field = 'irhpApplication';

                if (isset($responseDump['id']['ecmtPermitApplication'])) {
                    $field = 'ecmtPermitApplication';
                    $config['step'] = EcmtSection::ROUTE_APPLICATION_OVERVIEW;
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
        if (isset($this->queryParams['active']) && ($activeApplication['licence']['id'] == $this->queryParams['active'])) {
            $config['step'] = $overviewRoute;
            $this->redirectParams = ['id' => $activeApplication['id']];
        } else {
            $config['step'] = isset($config['command']) ? $addRoute : $changeRoute;
            $this->redirectOptions = [
                'query' => ['active' => $activeApplication['licence']['id']]
            ];
        }
        return $config;
    }

    /**
     * @return void|\Zend\Http\Response
     */
    public function checkConditionalDisplay()
    {
        $irhpPermitTypeID = RefData::ECMT_PERMIT_TYPE_ID;

        if (isset($this->data['application']['irhpPermitType']['id'])) {
            $irhpPermitTypeID = $this->data['application']['irhpPermitType']['id'];
        } elseif (isset($this->data['irhpPermitType']['id'])) {
            $irhpPermitTypeID = $this->data['irhpPermitType']['id'];
        }

        if ($irhpPermitTypeID !== RefData::ECMT_PERMIT_TYPE_ID) {
            unset($this->conditionalDisplayConfig['add'][LicencesAvailable::DATA_KEY]);
        }

        parent::checkConditionalDisplay();
    }
}
