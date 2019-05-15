<?php

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService as FormHelper;
use Common\Controller\AbstractOlcsController;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplicationStep;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ApplicationStep;
use Olcs\Service\Qa\FormProvider;
use Permits\View\Helper\EcmtSection;
use Permits\View\Helper\IrhpApplicationSection;
use Zend\View\Model\ViewModel;

class QaController extends AbstractOlcsController
{
    /** @var FormHelper */
    private $formHelper;

    /** @var FormProvider */
    private $formProvider;

    /**
     * Create service instance
     *
     * @param FormHelper $formHelper
     * @param FormProvider $formProvider
     *
     * @return QaController
     */
    public function __construct(FormHelper $formHelper, FormProvider $formProvider)
    {
        $this->formHelper = $formHelper;
        $this->formProvider = $formProvider;
    }

    public function indexAction()
    {
        $routeParams = $this->params()->fromRoute();

        $query = ApplicationStep::create(
            [
                'id' => $routeParams['id'],
                'slug' => $routeParams['slug'],
            ]
        );

        $response = $this->handleQuery($query);
        if (!$response->isOk()) {
            throw new RuntimeException('Non-success response received from backend');
        }

        $result = $response->getResult();

        $form = $this->formProvider->get($result['form']);

        // TODO: what does this line do and is it necessary?
        //$this->formHelper->setFormActionFromRequest($form, $this->getRequest());

        if ($this->request->isPost()) {
            $postParams = $this->params()->fromPost();
            $form->setData($postParams);

            if ($form->isValid()) {
                $command = SubmitApplicationStep::create(
                    [
                        'id' => $routeParams['id'],
                        'slug' => $routeParams['slug'],
                        'postData' => $postParams
                    ]
                );

                $this->handleCommand($command);

                if (isset($postParams['Submit']['SaveAndReturnButton'])) {
                    return $this->redirect()->toRoute(
                        IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
                        [
                            'id' => $routeParams['id']
                        ]
                    );
                }

                return $this->redirect()->toRoute(
                    $this->event->getRouteMatch()->getMatchedRouteName(),
                    [
                        'id' => $routeParams['id'],
                        'slug' => $result['nextStepSlug'],
                    ]
                );
            }
        }

        $templateVars = array_merge(
            $result['templateVars'],
            [
                'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
                'cancelUrl' => EcmtSection::ROUTE_PERMITS,
            ]
        );

        $view = new ViewModel();
        $view->setVariable('data', $templateVars);
        $view->setVariable('form', $form);
        $view->setTemplate('permits/single-question');

        return $view;
    }
}
