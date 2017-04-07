<?php

namespace Olcs\Controller\Lva;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Common\Controller\Lva\AbstractController;
use Dvsa\Olcs\Transfer\Command\Application\UpdateAuthSignature;

/**
* Internal Application Undertakings Controller
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
abstract class AbstractDeclarationsInternalController extends AbstractController implements
    ApplicationControllerInterface
{
    /**
     * indexAction
     *
     * @return \Common\View\Model\Section|\Zend\Http\Response
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $form = $this->getForm();
        $this->alterFormForLva($form);

        if ($request->isPost()) {
            $data = (array)$request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $formData = $form->getData();
                $response = $this->handleCommand(
                    UpdateAuthSignature::create(
                        [
                            'id' => $this->getApplicationId(),
                            'version' => $formData['version'],
                            'authSignature' => $formData['declarations']['declarationConfirmation'],
                        ]
                    )
                );
                if ($response->isOk()) {
                    return $this->completeSection('undertakings');
                } else {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
                }

            }
        } else {
            $applicationData = $this->getApplicationData($this->getApplicationId());
            $formData = [
                'version' => $applicationData['version'],
                'declarations' => [
                    'declarationConfirmation' => $applicationData['authSignature'] ? 'Y' : 'N'
                ],
            ];
            $form->setData($formData);
        }

        return $this->render('undertakings', $form);
    }

    /**
     * Get the Form
     *
     * @return \Zend\Form\Form
     */
    protected function getForm()
    {
        $form = $this->getServiceLocator()
            ->get('FormServiceManager')
            ->get('lva-' . $this->lva . '-undertakings')
            ->getForm();

        // populate the link
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $summaryDownload = $translator->translateReplace(
            'undertakings_summary_download',
            [
                $this->url()->fromRoute('lva-' . $this->lva . '/review', [], [], true),
                $translator->translate('view-full-application'),
            ]
        );
        $form->get('declarations')->get('summaryDownload')->setAttribute('value', $summaryDownload);

        return $form;
    }
}
