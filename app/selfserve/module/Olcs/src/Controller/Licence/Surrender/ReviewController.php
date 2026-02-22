<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\ReviewDetails;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Olcs\Form\Model\Form\Surrender\Review;
use Permits\Data\Mapper\MapperManager;

class ReviewController extends AbstractSurrenderController
{
    protected $formConfig = [
        'default' => [
            'continue' => [
                'formClass' => Review::class,
            ]
        ]
    ];

    protected $templateConfig = [
        'default' => 'licence/surrender-review'
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
     * @return \Laminas\Http\Response
     */
    #[\Override]
    public function confirmationAction()
    {
        $this->updateSurrender(RefData::SURRENDER_STATUS_DETAILS_CONFIRMED);
        return $this->nextStep('licence/surrender/destroy/GET', [], []);
    }

    /**
     * @return array
     *
     */
    protected function getViewVariables(): array
    {
        return [
            'title' => $this->translationHelper->translate('licence.surrender.review.heading'),
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'backLink' => $this->getLink('licence/surrender/operator-licence/GET'),
            'sections' => ReviewDetails::makeSections($this->data['surrender']['licence'], $this->url(), $this->translationHelper, $this->data),
        ];
    }

    #[\Override]
    public function alterForm($form)
    {
        $form->get('form-actions')->get('submit')->setLabel('licence.surrender.review.action');
        return parent::alterForm($form);
    }


    #[\Override]
    protected function getLink(string $route): string
    {
        if ($this->isInternationalLicence()) {
            $route = 'licence/surrender/community-licence/GET';
        }
        return parent::getLink($route);
    }
}
