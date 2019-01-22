<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\ReviewDetails;
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


    protected $dataSourceConfig = [
        'default' => DataSourceConfig::SURRENDER
    ];

    public function indexAction()
    {
        return $this->createView();
    }

    public function confirmationAction()
    {
        return $this->nextStep('licence/surrender/declaration/GET', [], []);
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
            'licNo' => $this->licence['licNo'],
            'backLink' => $this->getBackLink('licence/surrender/operator-licence/GET'),
            'sections' => ReviewDetails::makeSections($this->licence, $this->url(), $translator, $this->data),
        ];
    }

    public function alterForm($form)
    {
        $form->get('form-actions')->get('submit')->setLabel('licence.surrender.review.action');
        return parent::alterForm($form);
    }


    protected function getBackLink(string $route): string
    {
        if ($this->data['licence']['isInternationalLicence']) {
            $route = 'licence/surrender/community-licence/GET';
        }
        return parent::getBackLink($route);
    }
}
