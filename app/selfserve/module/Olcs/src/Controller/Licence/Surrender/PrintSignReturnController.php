<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Common\Util\FlashMessengerTrait;
use Dvsa\Olcs\Transfer\Command\Surrender\SubmitForm;
use Laminas\View\Model\ViewModel;
use Permits\Data\Mapper\MapperManager;

/**
 * Class PrintSignReturnController
 *
 * @package Olcs\Controller\Licence\Surrender
 */
class PrintSignReturnController extends AbstractSurrenderController
{
    use FlashMessengerTrait;

    protected $templateConfig = [
        'default' => 'licence/surrender-print-sign-return'
    ];

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
        return $this->createView();
    }


    /**
     * @return ViewModel|\Laminas\Http\Response
     */
    public function printAction()
    {
        $params = [
            'isNi' => $this->data['surrender']['licence']['niFlag'] === 'Y',
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'name' => $this->data['surrender']['licence']['organisation']['name'],
            'title' => $this->determineTitle(),
            'undertakings' => $this->translationHelper->translateReplace(
                'markup-licence-surrender-declaration',
                [$this->data['surrender']['licence']['licNo']]
            )
        ];
        $view = new ViewModel($params);
        $view->setTemplate('licence/surrender-print-sign-return-form');

        $layout = new ViewModel();
        $layout->setTemplate('layouts/simple');
        $layout->setTerminal(true);
        $layout->addChild($view, 'content');

        $response = $this->handleCommand(SubmitForm::create(
            [
                "id" => $this->licenceId,
                "version" => 1
            ]
        ));
        if ($response->isOk()) {
            return $layout;
        }

        $this->flashMessengerHelper->addErrorMessage('licence.surrender.print-sign-return.form.error');
        return $this->redirect()->toRoute('licence/surrender/print-sign-return/GET', [], [], true);
    }


    protected function determineTitle(): string
    {
        if ($this->data['surrender']['licence']['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            return "licence.surrender.print-sign-return.form.title.gv";
        }
        return "licence.surrender.print-sign-return.form.title.psv";
    }

    /**
     * @return array
     *
     */
    protected function getViewVariables(): array
    {

        return
            [
                'pageTitle' => 'licence.surrender.print-sign-return.page.title',
                'returnLinkText' => 'return-home-button-text',
                'returnLink' => $this->getLink('dashboard'),
                'printLink' => $this->getLink('licence/surrender/print-sign-return-print/GET'),
            ];
    }
}
