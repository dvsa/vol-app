<?php

namespace Permits\Controller;

use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Create;
use Dvsa\Olcs\Transfer\Query\Permits\AvailableStocks;
use Dvsa\Olcs\Transfer\Query\Permits\AvailableYears;
use Dvsa\Olcs\Transfer\Query\Permits\MaxPermittedReachedByStockAndLicence;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\LicencesAvailable;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;
use RuntimeException;

class LicenceController extends AbstractSelfserveController
{
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
            'cancelUri' => IrhpApplicationSection::ROUTE_PERMITS,
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
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

    /**
     * @return \Laminas\View\Model\ViewModel
     */
    public function addAction()
    {
        return $this->genericAction();
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    #[\Override]
    public function mergeTemplateVars()
    {
        $permitTypeId = $this->data[LicencesAvailable::DATA_KEY]['permitTypeId'];

        switch ($permitTypeId) {
            case RefData::ECMT_PERMIT_TYPE_ID:
                $this->templateVarsConfig['question']['backUri'] = IrhpApplicationSection::ROUTE_YEAR;
                $this->templateVarsConfig['question']['backUriOptions'] = [
                    'query' => [
                        'selected' => $this->getEcmtAnnualSelectedYear()
                    ]
                ];
                break;
            case RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID:
                $this->templateVarsConfig['question']['backUri'] = IrhpApplicationSection::ROUTE_STOCK;
                $this->templateVarsConfig['question']['backUriParams'] = [
                    'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'year' => $this->getEcmtShortTermSelectedYear()
                ];
                $this->templateVarsConfig['question']['backUriOptions'] = [
                    'query' => [
                        'selected' => $this->routeParams['stock']
                    ]
                ];
                break;
            case RefData::ECMT_REMOVAL_PERMIT_TYPE_ID:
            case RefData::IRHP_BILATERAL_PERMIT_TYPE_ID:
            case RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID:
            case RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID:
            case RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID:
                $this->templateVarsConfig['question']['backUri'] = IrhpApplicationSection::ROUTE_TYPE;
                $this->templateVarsConfig['question']['backUriOptions'] = [
                    'query' => [
                        'selected' => $permitTypeId
                    ]
                ];
                break;
            default:
                throw new RuntimeException('No back uri defined for permit type ' . $permitTypeId);
        }

        parent::mergeTemplateVars();
    }

    /**
     * Derive and return the selected year for ecmt annual using the stock id provided in the route params
     *
     * @return string
     */
    private function getEcmtAnnualSelectedYear()
    {
        $response = $this->handleQuery(
            AvailableYears::create(
                [
                    'type' => RefData::ECMT_PERMIT_TYPE_ID
                ]
            )
        );
        $result = $response->getResult();

        $stockId = $this->routeParams['stock'];

        return $result['years'][$stockId] ?? '';
    }

    /**
     * Derive and return the selected year for ecmt short term using the stock id provided in the route params
     *
     * @return string
     */
    private function getEcmtShortTermSelectedYear()
    {
        $response = $this->handleQuery(
            AvailableStocks::create(
                [
                    'irhpPermitType' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID
                ]
            )
        );
        $result = $response->getResult();

        $stockId = $this->routeParams['stock'];

        return $result['stocks'][$stockId]['year'] ?? '';
    }

    /**
     * @param array $config
     * @param array $params
     *
     * @return void
     */
    #[\Override]
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

        if ($licencesAvailable['isEcmtAnnual']) {
            $response = $this->handleQuery(
                MaxPermittedReachedByStockAndLicence::create([
                    'irhpPermitStock' => $this->routeParams['stock'],
                    'licence' => $params['licence']
                ])
            );

            $result = $response->getResult();

            if ($result['maxPermittedReached']) {
                $config['step'] = IrhpApplicationSection::ROUTE_MAX_PERMITTED_REACHED_FOR_STOCK;

                $this->redirectParams = [
                    'id' => $this->getCurrentOrganisationId(),
                    'irhpPermitStock' => $params['irhpPermitStock'],
                    'licence' => $params['licence']
                ];

                return;
            }
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
