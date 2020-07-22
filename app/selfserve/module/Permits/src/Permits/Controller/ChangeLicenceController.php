<?php
namespace Permits\Controller;

use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\LicencesAvailable;
use Permits\Controller\Config\Form\FormConfig;
use Permits\View\Helper\IrhpApplicationSection;

class ChangeLicenceController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'question' => DataSourceConfig::PERMIT_APP_CHANGE_LICENCE,
    ];

    protected $conditionalDisplayConfig = [
        'question' => ConditionalDisplayConfig::PERMIT_APP_CAN_APPLY_LICENCE_EXISTING_APP,
    ];

    protected $formConfig = [
        'question' => FormConfig::FORM_LICENCE,
    ];

    protected $templateConfig = [
        'default' => 'permits/single-question'
    ];

    protected $templateVarsConfig = [
        'question' => [
            'browserTitle' => 'permits.page.licence.browser.title',
            'question' => 'permits.page.licence.question',
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW
        ],
    ];

    protected $postConfig = [
        'question' => [
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
     * @param array $config
     * @param array $params
     */
    public function handlePostCommand(array &$config, array $params)
    {
        $licenceData = $this->data[LicencesAvailable::DATA_KEY]['eligibleLicences'][$params['licence']];
        $this->redirectParams = ['licence' => $params['licence']];

        if (isset($licenceData['activeApplicationId'])) {
            $config['step'] = IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW;
            $this->redirectParams = ['id' => $licenceData['activeApplicationId']];
            unset($config['command']);
        }
    }
}
