<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Data\Mapper\Licence\Surrender\ReviewContactDetails;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Query\Licence\LicenceWithCorrespondenceCd as LicenceQuery;
use Olcs\Controller\AbstractSelfserveController;
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
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');

        $params = [
            'title' => 'licence.surrender.review_contact_details.title',
            'licNo' => $this->licence['licNo'],
            'content' => 'licence.surrender.review_contact_details.content',
            'note' => 'licence.surrender.review_contact_details.note',
            'form' => $this->getConfirmationForm(),
            'backLink' => $this->url()->fromRoute('licence/surrender/start', ['licence' => $this->licence['id']]),
            'sections' => ReviewContactDetails::makeSections($this->licence, $this->url(), $translator),
        ];

        $view = new ViewModel($params);
        $view->setTemplate('pages/licence-surrender-reviewContactDetails');

        return $view;
    }

    public function confirmAction()
    {
        // To be implemented
    }


    private function getLicence()
    {
        $response = $this->handleQuery(
            LicenceQuery::create(['id' => $this->licenceId])
        );

        return $response->getResult();
    }

    private function getConfirmationForm(): \Common\Form\Form
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');

        /* @var $form \Common\Form\GenericConfirmation */
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
        $submitLabel = $translator->translate('confirm-and-continue');
        $form->setSubmitLabel($submitLabel);
        $form->removeCancel();
        return $form;
    }
}
