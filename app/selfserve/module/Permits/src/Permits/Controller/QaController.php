<?php

namespace Permits\Controller;

use Common\Controller\AbstractOlcsController;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplicationStep;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ApplicationStep;
use Olcs\Service\Qa\FormProvider;
use Olcs\Service\Qa\TemplateVarsGenerator;
use Olcs\Service\Qa\ViewGeneratorProvider;
use Permits\View\Helper\IrhpApplicationSection;
use RuntimeException;
use Zend\View\Model\ViewModel;

class QaController extends AbstractOlcsController
{
    const FIELDSET_DATA_PREFIX = 'fieldset';

    /** @var FormProvider */
    private $formProvider;

    /** @var TemplateVarsGenerator */
    private $templateVarsGenerator;

    /** @var TranslationHelperService */
    private $translationHelperService;

    /** @var ViewGeneratorProvider */
    private $viewGeneratorProvider;

    /**
     * Create service instance
     *
     * @param FormProvider $formProvider
     * @param TemplateVarsGenerator $templateVarsGenerator
     * @param TranslationHelperService $translationHelperService
     * @param ViewGeneratorProvider $viewGeneratorProvider
     *
     * @return QaController
     */
    public function __construct(
        FormProvider $formProvider,
        TemplateVarsGenerator $templateVarsGenerator,
        TranslationHelperService $translationHelperService,
        ViewGeneratorProvider $viewGeneratorProvider
    ) {
        $this->formProvider = $formProvider;
        $this->templateVarsGenerator = $templateVarsGenerator;
        $this->translationHelperService = $translationHelperService;
        $this->viewGeneratorProvider = $viewGeneratorProvider;
    }

    /**
     * Index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $routeName = $this->event->getRouteMatch()->getMatchedRouteName();
        $viewGenerator = $this->viewGeneratorProvider->getByRouteName($routeName);

        $routeParams = $this->params()->fromRoute();

        $irhpPermitApplicationId = null;
        if (isset($routeParams['irhpPermitApplication'])) {
            $irhpPermitApplicationId = $routeParams['irhpPermitApplication'];
        }

        $applicationStepParams = [
            'id' => $routeParams['id'],
            'irhpPermitApplication' => $irhpPermitApplicationId,
            'slug' => $routeParams['slug'],
        ];

        $query = ApplicationStep::create($applicationStepParams);

        $response = $this->handleQuery($query);
        if (!$response->isOk()) {
            throw new RuntimeException('Non-success response received from backend');
        }

        $result = $response->getResult();

        $form = $this->formProvider->get(
            $result['applicationStep'],
            $viewGenerator->getFormName()
        );

        $showErrorInBrowserTitle = false;

        if ($this->request->isPost()) {
            $postParams = $this->params()->fromPost();
            $form->setData($postParams);

            if ($form->isValid()) {
                $formData = $form->getData();
                $commandData = ['qa' => $formData['qa']];

                $submitApplicationStepParams = array_merge(
                    $applicationStepParams,
                    ['postData' => $commandData]
                );

                $command = SubmitApplicationStep::create($submitApplicationStepParams);
                $response = $this->handleCommand($command);

                $submitApplicationStepResult = $response->getResult();
                $resultMessages = $submitApplicationStepResult['messages'];

                if (count($resultMessages) != 1) {
                    throw new RuntimeException('SubmitApplicationStep must return exactly one message');
                }

                $destinationName = $resultMessages[0];
                if ($destinationName != 'NEXT_STEP') {
                    return $viewGenerator->handleRedirectionRequest(
                        $this->redirect(),
                        $destinationName
                    );
                }

                if (isset($postParams['Submit']['SaveAndReturnButton'])) {
                    return $this->redirect()->toRoute(
                        IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
                        [
                            'id' => $routeParams['id']
                        ]
                    );
                }

                return $this->redirect()->toRoute(
                    $routeName,
                    array_merge(
                        $routeParams,
                        ['slug' => $result['nextStepSlug']]
                    )
                );
            }

            $showErrorInBrowserTitle = true;

            // transfer data normalised by input filter back into form, don't touch anything apart from the
            // Q&A fieldset content to avoid unwanted form breakage
            $normalisedData = $form->getData();
            foreach ($normalisedData['qa'] as $key => $value) {
                $postParams['qa'][$key] = $value;
            }

            $form->setDataForRedisplay($postParams);
        }

        $templateVars = array_merge(
            $this->templateVarsGenerator->generate($result['questionText']),
            $viewGenerator->getAdditionalViewVariables($this->event, $result)
        );

        $pageTitle = $this->translationHelperService->translate($result['title']);
        $this->placeholder()->setPlaceholder('pageTitle', $pageTitle);
        if ($showErrorInBrowserTitle) {
            $this->placeholder()->setPlaceholder(
                'pageTitle',
                $this->translationHelperService->translate('permits.application.browser.title.error').': '.$pageTitle
            );
        }

        $view = new ViewModel();
        $view->setVariable('data', $templateVars);
        $view->setVariable('form', $form);

        $view->setTemplate(
            $viewGenerator->getTemplateName()
        );

        return $view;
    }
}
