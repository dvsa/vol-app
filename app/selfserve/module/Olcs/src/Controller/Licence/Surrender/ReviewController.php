<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\ReviewDetails;
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
            'title' => 'TEMPORARY page for completeion in OLCS-22260',
            'licNo' => $this->licence['licNo'],
            'backLink' => $this->getBackLink('lva-licence'),
            'sections' => ReviewDetails::makeSections($this->licence, $this->url(), $translator),
        ];
    }
}
