<?php

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpPermitType;
use Permits\Controller\Config\Table\TableConfig;
use Permits\Data\Mapper\MapperManager;

class IrhpValidPermitsController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'generic' => DataSourceConfig::IRHP_VALID,
    ];

    protected $tableConfig = [
        'generic' => TableConfig::VALID_IRHP_PERMITS,
    ];

    protected $templateConfig = [
        'generic' => 'permits/irhp-valid-permits',
    ];

    protected $templateVarsConfig = [
        'generic' => []
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
     * @return void
     */
    #[\Override]
    public function mergeTemplateVars()
    {
        // overwrite default page title
        $title = $this->data[IrhpPermitType::DATA_KEY]['name']['description'];

        $this->templateVarsConfig['generic']['browserTitle'] = $title;
        $this->templateVarsConfig['generic']['title'] = $title;

        $selectedCountryId = $this->queryParams['country'] ?? null;
        $this->data['selectedCountryId'] = $selectedCountryId;

        parent::mergeTemplateVars();
    }

    /**
     * @return void
     */
    #[\Override]
    public function retrieveTables()
    {
        if ($this->data[IrhpPermitType::DATA_KEY]['isBilateral']) {
            $this->tableConfig = [
                'generic' => TableConfig::VALID_IRHP_PERMITS_BILATERAL,
            ];
        } elseif ($this->data[IrhpPermitType::DATA_KEY]['isMultilateral']) {
            $this->tableConfig = [
                'generic' => TableConfig::VALID_IRHP_PERMITS_MULTILATERAL,
            ];
        } elseif ($this->data[IrhpPermitType::DATA_KEY]['isEcmtShortTerm']) {
            $this->tableConfig = [
                'generic' => TableConfig::VALID_IRHP_PERMITS_ECMT_SHORT_TERM,
            ];
        } elseif ($this->data[IrhpPermitType::DATA_KEY]['isEcmtRemoval']) {
            $this->tableConfig = [
                'generic' => TableConfig::VALID_IRHP_PERMITS_ECMT_REMOVAL,
            ];
        }

        parent::retrieveTables();

        $this->data['selectedLimit'] = $this->tables['valid-irhp-permits']->getLimit();
    }
}
