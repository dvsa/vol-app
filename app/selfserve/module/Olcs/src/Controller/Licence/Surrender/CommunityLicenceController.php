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
        'index' => 'licence/surrender-community-licence',
        'submit' => 'licence/surrender-community-licence'
    ];

    public function indexAction()
    {

        $surrender = $this->getSurrender();
        $view = $this->genericView();
        $view->setVariables(
            [
                'pageTitle' => 'licence.surrender.community_licence.heading',
                'licNo' => $surrender['licence']['licNo'],
                'backUrl' => $this->getBackLink('licence/surrender/operator-licence'),
                'returnLinkText' => 'licence.surrender.community_licence.return_to_operator.licence.link',
                'returnLink' => $this->getBackLink('licence/surrender/operator-licence'),
            ]
        );

        return $view;
    }

    public function submitAction()
    {
        $form = $this->getForm(CommunityLicence::class);
        $formData = (array)$this->getRequest()->getPost();
        $form->setData($formData);
        $validForm = $form->isValid();
        if ($validForm) {
            //save data
            $view = $this->genericView();
            return $view;
        }
    }

    public function alterForm($form)
    {
        $possessionLabel = $form->get('communityLicence')->get('possessionContent')->get('notice');
        $possessionLabel->setLabel('licence.surrender.community_licence.possession.note');
        $formActionButton = $form->get('form-actions')->get('submit');
        $formActionButton->setLabel('Save and Continue');
        return $form;
    }
}
