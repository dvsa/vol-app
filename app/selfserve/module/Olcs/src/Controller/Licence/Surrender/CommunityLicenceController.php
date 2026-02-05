<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\CommunityLicence as Mapper;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Olcs\Controller\Config\DataSource\Surrender;
use Olcs\Form\Model\Form\Surrender\CommunityLicence;
use Permits\Data\Mapper\MapperManager;

/**
 * Class CommunityLicenceController
 *
 * @package Olcs\Controller\Licence\Surrender
 */
class CommunityLicenceController extends AbstractSurrenderController
{
    use ReviewRedirect;

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
        'default' => 'licence/surrender-licence-documents'
    ];

    protected $conditionalDisplayConfig = [
        'default' => [
            [
                'source' => Surrender::DATA_KEY,
                'key' => 'isInternationalLicence',
                'value' => true,
                'route' => 'licence/surrender/review/GET'
            ]
        ]
    ];

    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     * @param FlashMessengerHelperService $hlpFlashMsgr
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager,
        FlashMessengerHelperService $hlpFlashMsgr
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager, $hlpFlashMsgr);
    }

    #[\Override]
    public function indexAction()
    {
         return $this->createView();
    }

    public function submitAction(): \Laminas\View\Model\ViewModel
    {

        $formData = (array)$this->getRequest()->getPost();
        $this->form->setData($formData);
        $validForm = $this->form->isValid();
        if ($validForm) {
            $data = $this->mapperManager->get(Mapper::class)->mapFromForm($formData);
            if ($this->updateSurrender(RefData::SURRENDER_STATUS_COMM_LIC_DOCS_COMPLETE, $data)) {
                $routeName = 'licence/surrender/review/GET';
                $this->nextStep($routeName);
            }
        }
        return $this->createView();
    }

    #[\Override]
    public function alterForm($form)
    {
        $form->get('form-actions')->get('submit')->setLabel($this->translationHelper->translate('lva.external.save_and_continue.button'));
        return $form;
    }


    /**
     * @return array
     */
    protected function getViewVariables(): array
    {
        return [
            'pageTitle' => 'licence.surrender.community_licence.heading',
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'backUrl' => $this->getLink('licence/surrender/operator-licence/GET'),
            'returnLinkText' => 'licence.surrender.community_licence.return_to_operator.licence.link',
            'returnLink' => $this->getLink('licence/surrender/operator-licence/GET'),
        ];
    }
}
