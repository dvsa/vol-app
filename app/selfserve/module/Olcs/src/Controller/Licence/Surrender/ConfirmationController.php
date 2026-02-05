<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Query\Surrender\GetSignature;
use Permits\Data\Mapper\MapperManager;

class ConfirmationController extends AbstractSurrenderController
{
    protected $pageTemplate = 'pages/confirmation';
    protected $dataSourceConfig = [];

    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     * @param FlashMessengerHelperService $flashMessengerHelper
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager,
        FlashMessengerHelperService $flashMessengerHelper
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager, $flashMessengerHelper);
    }

    #[\Override]
    public function indexAction()
    {
        $params = $this->getViewVariables();
        $this->updateSurrender(RefData::SURRENDER_STATUS_SUBMITTED);
        return $this->renderView($params);
    }

    private function getSignatureFullName(): string
    {
        $names = [];
        $attributes = json_decode((string) $this->data['surrender']["digitalSignature"]["attributes"]);
        $names[] = $attributes->firstname ?? '';
        $names[] = $attributes->surname ?? '';

        return implode(' ', $names);
    }

    private function getSignatureDate(): string
    {
        $unixTimeStamp = strtotime((string) $this->data['surrender']["digitalSignature"]['createdOn']);
        return date("j M Y", $unixTimeStamp);
    }

    private function returnDashboardLink(): string
    {
        return $this->url()->fromRoute('dashboard');
    }

    protected function getSurrender()
    {
        $result = $this->handleQuery(GetSignature::create(
            ['id' => $this->licenceId]
        ));
        return $result->getResult();
    }

    /**
     * @return array
     *
     */
    protected function getViewVariables(): array
    {
        $this->data['surrender'] = $this->getSurrender();
        return [
            'content' => $this->translationHelper->translateReplace(
                'markup-licence-surrender-confirmation',
                [
                    $this->data['surrender']['licence']['licNo'],
                    $this->getSignatureFullName(),
                    $this->getSignatureDate(),
                    $this->returnDashboardLink()
                ]
            ),
            'backLink' => $this->returnDashboardLink()
        ];
    }
}
