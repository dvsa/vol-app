<?php

namespace Permits\Controller;

use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\AvailableTypes;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;

class TypeController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'question' => DataSourceConfig::PERMIT_APP_TYPE,
    ];

    protected $conditionalDisplayConfig = [
        'question' => ConditionalDisplayConfig::PERMIT_APP_CAN_APPLY,
    ];

    protected $formConfig = [
        'question' => FormConfig::FORM_TYPE,
    ];

    protected $templateConfig = [
        'default' => 'permits/single-question'
    ];

    protected $templateVarsConfig = [
        'question' => [
            'browserTitle' => 'permits.page.type.title',
            'question' => 'permits.page.type.question',
            'backUri' => IrhpApplicationSection::ROUTE_PERMITS,
            'cancelUri' => IrhpApplicationSection::ROUTE_PERMITS,
            'headingType' => 'permit-type',
        ],
    ];

    protected $postConfig = [
        'question' => [
            'step' => IrhpApplicationSection::ROUTE_ADD_LICENCE,
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
     * {@inheritdoc}
     *
     * @return void
     */
    #[\Override]
    public function retrieveData()
    {
        parent::retrieveData();

        $selectedType = '';
        if (isset($this->queryParams['selected'])) {
            $selectedType = $this->queryParams['selected'];
        }

        $this->data[AvailableTypes::DATA_KEY]['selectedType'] = $selectedType;
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
        $yearBasedTypes = [
            RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            RefData::ECMT_PERMIT_TYPE_ID,
        ];

        if (in_array($params['type'], $yearBasedTypes)) {
            $config['step'] = IrhpApplicationSection::ROUTE_YEAR;
        }

        $this->redirectParams = [
            'type' => $params['type']
        ];
    }
}
