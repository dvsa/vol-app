<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Data\Mapper\Licence\Surrender\Sections\LicenceDetails;
use Dvsa\Olcs\Transfer\Query\Licence\Addresses as AddressesQuery;
use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceQuery;
use Olcs\Controller\AbstractSelfserveController;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class ReviewContactDetailsController extends AbstractSelfserveController implements ToggleAwareInterface
{

    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_SURRENDER_ENABLED
    ];

    protected $licenceId;
    protected $surrenderId;
    protected $licence;

    public function onDispatch(MvcEvent $e)
    {
        $this->licenceId = (int)$this->params('licence');
        $this->surrenderId = (int)$this->params('surrender');
        $this->licence = $this->getLicence();

        return parent::onDispatch($e);
    }

    public function indexAction()
    {
        $params = [
            'title' => 'Review your contact information',
            'licNo' => $this->licence['licNo'],
            'content' => 'contenuto',
        ];

        $licenceDetails = new LicenceDetails();
        $sections = [
            $licenceDetails->makeSection($this->licence),
//            [
//                'sectionHeading' => 'The section heading',
//                'changeLinkInHeading' => true,
//                'change' => [
//                    'sectionLink' => 'the/section/link',
//                ],
//                'questions' => [
//                    [
//                        'label' => 'the label',
//                        'answer' => 'the answer',
//                        'changeLinkInHeading' => true,
//                    ],
//                ]
//            ]
        ];

        $params['sections'] = $sections;

        $view = new ViewModel($params);
        $view->setTemplate('pages/licence-surrender-reviewContactDetails');

        return $view;
    }

    public function confirmAction()
    {
        echo "confirm action";
    }


    private function getLicence()
    {
        $response = $this->handleQuery(
            LicenceQuery::create(['id' => $this->licenceId])
        );

        return $response->getResult();
    }

    /**
     * getPageLayout
     *
     * @param object $translator
     * @param array  $transportManagerApplication
     * @param int    $transportManagerApplicationId
     *
     * @return array
     */
    private function getPageLayout($translator): array
    {
        $checkAnswersHint = $translator->translate('lva.section.transport-manager-check-answers-hint');
        $title = 'check_answers';
        $defaultParams = [
            'content' => $checkAnswersHint,
            'licNo' => $this->licence['licNo'],
            'backLink' => $this->url()->fromRoute(
                'licence/surrender/start',
                [
                    'action' => 'confirm',
                    'licence' => $this->licenceId
                ]
            )
        ];

        $form = $this->getConfirmationForm();
        return array($title, $defaultParams, $form);
    }

    private function getConfirmationForm(): \Common\Form\Form
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        /* @var $form \Common\Form\Form */
        $form = $formHelper->createForm('GenericConfirmation');
        $form->setAttribute(
            "action",
            $this->url()->fromRoute(
                'licence/surrender/review-contact-details',
                [
                    'action' => 'confirm',
                    'licence' => $this->licenceId,
                    'surrender' => $this->surrenderId
                ]
            )
        );
        $submitLabel = 'Confirm and continue';
        $form->setSubmitLabel($submitLabel);
        $form->removeCancel();
        return $form;
    }

    private function mapForSections($data, $translator)
    {

    }

}
