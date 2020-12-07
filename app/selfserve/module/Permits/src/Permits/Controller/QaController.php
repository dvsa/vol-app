<?php

namespace Permits\Controller;

use Common\Category;
use Common\Controller\AbstractOlcsController;
use Common\Controller\Traits\GenericUpload;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\DataTransformer\ApplicationStepsPostDataTransformer;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplicationStep;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ApplicationStep;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ById;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\Documents;
use Olcs\Service\Qa\FormProvider;
use Olcs\Service\Qa\TemplateVarsGenerator;
use Olcs\Service\Qa\ViewGeneratorProvider;
use Permits\View\Helper\IrhpApplicationSection;
use RuntimeException;
use Laminas\View\Model\ViewModel;

class QaController extends AbstractOlcsController
{
    const FIELDSET_DATA_PREFIX = 'fieldset';

    const UPLOADED_FILE_CATEGORY = Category::CATEGORY_PERMITS;
    const UPLOADED_FILE_SUBCATEGORY = Category::DOC_SUB_CATEGORY_MOT_CERTIFICATE;

    use GenericUpload;

    /** @var FormProvider */
    private $formProvider;

    /** @var TemplateVarsGenerator */
    private $templateVarsGenerator;

    /** @var TranslationHelperService */
    private $translationHelperService;

    /** @var ViewGeneratorProvider */
    private $viewGeneratorProvider;

    /** @var ApplicationStepsPostDataTransformer */
    private $applicationStepsPostDataTransformer;

    /** @var array */
    protected $documents;

    /** @var int */
    protected $irhpApplicationId;

    /**
     * Create service instance
     *
     * @param FormProvider $formProvider
     * @param TemplateVarsGenerator $templateVarsGenerator
     * @param TranslationHelperService $translationHelperService
     * @param ViewGeneratorProvider $viewGeneratorProvider
     * @param ApplicationStepsPostDataTransformer $applicationStepsPostDataTransformer
     *
     * @return QaController
     */
    public function __construct(
        FormProvider $formProvider,
        TemplateVarsGenerator $templateVarsGenerator,
        TranslationHelperService $translationHelperService,
        ViewGeneratorProvider $viewGeneratorProvider,
        ApplicationStepsPostDataTransformer $applicationStepsPostDataTransformer
    ) {
        $this->formProvider = $formProvider;
        $this->templateVarsGenerator = $templateVarsGenerator;
        $this->translationHelperService = $translationHelperService;
        $this->viewGeneratorProvider = $viewGeneratorProvider;
        $this->applicationStepsPostDataTransformer = $applicationStepsPostDataTransformer;
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
        $this->irhpApplicationId = $routeParams['id'];

        $irhpPermitApplicationId = null;
        if (isset($routeParams['irhpPermitApplication'])) {
            $irhpPermitApplicationId = $routeParams['irhpPermitApplication'];
        }

        $applicationStepParams = [
            'id' => $this->irhpApplicationId,
            'irhpPermitApplication' => $irhpPermitApplicationId,
            'slug' => $routeParams['slug'],
        ];

        $query = ApplicationStep::create($applicationStepParams);

        $response = $this->handleQuery($query);
        if (!$response->isOk()) {
            throw new RuntimeException('Non-success response received from backend');
        }

        $result = $response->getResult();

        $applicationStep = $result['applicationStep'];
        $form = $this->formProvider->get(
            $applicationStep,
            $viewGenerator->getFormName()
        );

        $hasProcessedFiles = false;
        if ($form->has('MultipleFileUpload')) {
            $hasProcessedFiles = $this->processFiles(
                $form,
                'MultipleFileUpload',
                [$this, 'processFileUpload'],
                [$this, 'deleteFile'],
                [$this, 'getDocuments']
            );

            if (!empty($form->getMessages())) {
                $form->preventSuccessfulValidation();
            }
        }

        $showErrorInBrowserTitle = false;

        if ($this->request->isPost()) {
            $postParams = $this->params()->fromPost();
            $form->setData($postParams);

            if ($form->isValid() && !$hasProcessedFiles) {
                $formData = $form->getData();

                $transformedFieldsetContent = $this->applicationStepsPostDataTransformer->getTransformed(
                    [$applicationStep],
                    $formData['qa']
                );

                $commandData = ['qa' => $transformedFieldsetContent];

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

    /**
     * Handle the file upload
     *
     * @param array $file File
     */
    public function processFileUpload(array $file)
    {
        $this->documents = null;

        $query = ById::create(['id' => $this->irhpApplicationId]);
        $response = $this->handleQuery($query);
        $result = $response->getResult();

        $licenceId = $result['licence']['id'];

        $data = [
            'description' => $file['name'],
            'category' => self::UPLOADED_FILE_CATEGORY,
            'subCategory' => self::UPLOADED_FILE_SUBCATEGORY,
            'isExternal'  => true,
            'licence' => $licenceId,
            'irhpApplication' => $this->irhpApplicationId,
        ];

        $this->uploadFile($file, $data);
    }

    /**
     * Get documents relating to the application
     *
     * @return array
     */
    public function getDocuments()
    {
        if ($this->documents === null) {
            $params = [
                'id' => $this->irhpApplicationId,
                'category' => self::UPLOADED_FILE_CATEGORY,
                'subCategory' => self::UPLOADED_FILE_SUBCATEGORY,
            ];

            $response = $this->handleQuery(Documents::create($params));
            $this->documents = $response->getResult();
        }

        return $this->documents;
    }
}
