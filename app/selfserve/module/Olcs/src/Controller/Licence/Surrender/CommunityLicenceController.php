<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\RefData;
use Olcs\Controller\Config\DataSource\DataSourceConfig;
use Olcs\Form\Model\Form\Surrender\CommunityLicence;
use Common\Data\Mapper\Licence\Surrender\CommunityLicence as Mapper;

/**
 * Class CommunityLicenceController
 *
 * @package Olcs\Controller\Licence\Surrender
 */
class CommunityLicenceController extends AbstractSurrenderController
{
    protected $formConfig = [
        'default' => [
            'communityLicenceForm' => [
                'formClass' => CommunityLicence::class,
                'mapper' => [
                    'class' => Mapper::class
                ],
                'dataSource' => 'surrender'
            ]
        ]
    ];

    protected $templateConfig = [
        'index' => 'licence/surrender-community-licence',
        'submit' => 'licence/surrender-community-licence'
    ];


    protected $dataSourceConfig = [
        'default' => DataSourceConfig::SURRENDER
    ];

    protected $conditionalDisplayConfig = [
        'default' => [
            'licence' => [
                'key' => 'isInternationalLicence',
                'value' => true,
                'route' => 'licence/surrender/review'
            ]
        ]
    ];

    public function indexAction()
    {
        $view = $this->createView($this->data['surrender']);
        return $view;
    }

    public function submitAction()
    {

        $formData = (array)$this->getRequest()->getPost();
        $this->form->setData($formData);
        $validForm = $this->form->isValid();
        if ($validForm) {
            $data = Mapper::mapFromForm($formData);
            if ($this->updateSurrender(RefData::SURRENDER_STATUS_COMM_LIC_DOCS_COMPLETE, $data)) {
                $routeName = 'licence/surrender/review';
                $this->nextStep($routeName);
            }
        }
        return $this->createView($this->getSurrender());
    }

    public function alterForm($form)
    {
        $form->get('form-actions')->get('submit')->setLabel('Save and Continue');
        return $form;
    }

    /**
     * @param array $surrender
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function createView(array $surrender): \Zend\View\Model\ViewModel
    {
        $view = $this->genericView();
        $view->setVariables(
            [
                'pageTitle' => 'licence.surrender.community_licence.heading',
                'licNo' => $surrender['licence']['licNo'],
                'backUrl' => $this->getBackLink('licence/surrender/operator-licence/GET'),
                'returnLinkText' => 'licence.surrender.community_licence.return_to_operator.licence.link',
                'returnLink' => $this->getBackLink('licence/surrender/operator-licence/GET'),
            ]
        );
        return $view;
    }
}
