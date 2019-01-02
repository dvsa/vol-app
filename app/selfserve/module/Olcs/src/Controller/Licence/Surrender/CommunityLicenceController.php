<?php

namespace Olcs\Controller\Licence\Surrender;

use Olcs\Form\Model\Form\Surrender\CommunityLicence;

class CommunityLicenceController extends AbstractSurrenderController
{
    protected $formConfig = [
        'index' => [
            'communityLicenceForm' => [
                'formClass' => CommunityLicence::class
            ]
        ]
    ];

    protected $templateConfig = [
        'index' => 'licence/surrender-community-licence'
    ];

    public function indexAction()
    {

        $surrender = $this->getSurrender();

        $view = $this->genericView();

        $view->setVariables(
            [
                'pageTitle' => "Where is your community licence documentation?",//@todo translation string
                'licNo' =>$surrender['licence']['licNo'],
                'backUrl' =>'',
            ]
        );

        return $view;
    }

    public function alterForm($form)
    {
        $options = $form->get('communityLicence')->get('communityLicenceDocument')->getOptions();
        $options['value_options']['possession']['label'] = 'test';
        $form->get('communityLicence')->get('communityLicenceDocument')->setOptions($options);
        return $form;
    }
}
