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
use Permits\View\Helper\IrhpApplicationSection;

class LicenceController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'question' => DataSourceConfig::PERMIT_APP_ADD_LICENCE,
    ];

    protected $conditionalDisplayConfig = [
        'question' => ConditionalDisplayConfig::PERMIT_APP_CAN_APPLY_LICENCE,
    ];

    protected $formConfig = [
        'question' => FormConfig::FORM_ADD_LICENCE,
    ];

    protected $templateConfig = [
        'default' => 'permits/single-question'
    ];

    protected $templateVarsConfig = [
        'question' => [
            'browserTitle' => 'permits.page.licence.browser.title',
            'question' => 'permits.page.licence.question',
            'backUri' => IrhpApplicationSection::ROUTE_TYPE
        ],
    ];

    protected $postConfig = [
        'question' => [
            'command' => Create::class,
            'params' => ParamsConfig::NEW_APPLICATION,
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
        $licencesAvailable = $this->data[LicencesAvailable::DATA_KEY];

        $nextStep = $licencesAvailable['isBilateral']
            ? IrhpApplicationSection::ROUTE_COUNTRIES
            : IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW;

        $config['step'] = $nextStep;

        $licenceData = $licencesAvailable['eligibleLicences'][$params['licence']];

        if (isset($licenceData['activeApplicationId'])) {
            $config = $this->handleActiveApplicationResponse(
                $config,
                [
                    'id' => $licenceData['activeApplicationId'],
                    'licence' => $params['licence'],
                ],
                IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
                IrhpApplicationSection::ROUTE_ADD_LICENCE
            );

            return;
        }

        //quick fix for mismatched route params
        $params['irhpPermitType'] = $params['type'];
        $params['irhpPermitStock'] = $this->routeParams['stock'] ?? null;

        /** @var CommandInterface $command */
        $command = $config['command']::create($params);

        $response = $this->handleCommand($command);
        $responseDump = $this->handleResponse($response);
        $this->redirectParams = ['id' => $responseDump['id']['irhpApplication']];
    }

    /**
     * Common code to handle response/config after Active Application query.
     *
     * @param array $config
     * @param array $activeApplication
     * @param string $overviewRoute
     * @param string $addRoute
     * @return array
     */
    protected function handleActiveApplicationResponse(array $config, array $activeApplication, string $overviewRoute, string $addRoute)
    {
        if (isset($this->queryParams['active']) && ($activeApplication['licence'] == $this->queryParams['active'])) {
            $config['step'] = $overviewRoute;
            $this->redirectParams = ['id' => $activeApplication['id']];
        } else {
            $config['step'] = $addRoute;
            $this->redirectOptions = [
                'query' => ['active' => $activeApplication['licence']]
            ];
        }
        return $config;
    }
}
