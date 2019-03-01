<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\ReviewDetails;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Olcs\Controller\Config\DataSource\DataSourceConfig;
use Olcs\Form\Model\Form\Surrender\Review;

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

    public function indexAction()
    {
        return $this->createView();
    }

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
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        return [
            'title' => $translator->translate('licence.surrender.review.heading'),
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'backLink' => $this->getLink('licence/surrender/operator-licence/GET'),
            'sections' => ReviewDetails::makeSections($this->data['surrender']['licence'], $this->url(), $translator, $this->data),
        ];
    }

    public function alterForm($form)
    {
        $form->get('form-actions')->get('submit')->setLabel('licence.surrender.review.action');
        return parent::alterForm($form);
    }


    protected function getLink(string $route): string
    {
        if ($this->isInternationalLicence()) {
            $route = 'licence/surrender/community-licence/GET';
        }
        return parent::getLink($route);
    }
}
