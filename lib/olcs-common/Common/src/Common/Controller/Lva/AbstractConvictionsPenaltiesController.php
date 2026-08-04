<?php

namespace Common\Controller\Lva;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Application\UpdatePreviousConvictions;
use Dvsa\Olcs\Transfer\Command\PreviousConviction\CreatePreviousConviction;
use Dvsa\Olcs\Transfer\Command\PreviousConviction\DeletePreviousConviction;
use Dvsa\Olcs\Transfer\Command\PreviousConviction\UpdatePreviousConviction;
use Dvsa\Olcs\Transfer\Query\Application\PreviousConvictions;
use Dvsa\Olcs\Transfer\Query\PreviousConviction\PreviousConviction;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Shared logic between Convictions Penalties controllers
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractConvictionsPenaltiesController extends AbstractController
{
    use Traits\CrudTableTrait;

    protected $section = 'convictions_penalties';

    protected string $baseRoute = 'lva-%s/convictions_penalties';

    protected FormHelperService $formHelper;

    protected FlashMessengerHelperService $flashMessengerHelper;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        protected FormServiceManager $formServiceManager,
        protected TableFactory $tableFactory,
        protected ScriptFactory $scriptFactory
    ) {
        $this->formHelper = $formHelper;
        $this->flashMessengerHelper = $flashMessengerHelper;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Index Action
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        $request = $this->getRequest();

        $response = $this->handleQuery(
            PreviousConvictions::create(['id' => $this->getIdentifier()])
        );

        $result = $response->getResult();

        $data = $request->isPost() ? (array)$request->getPost() : $this->getFormData($result);

        $form = $this->getConvictionsPenaltiesForm($result)->setData($data);

        $this->alterFormForLva($form);

        if ($request->isPost()) {
            $crudAction = $this->getCrudAction([$data['data']['table']]);

            if ($crudAction !== null) {
                $this->formHelper->disableEmptyValidation($form);
            }

            if ($form->isValid()) {
                $formData = $form->getData();

                $dto = UpdatePreviousConvictions::create(
                    [
                        'id' => $this->getIdentifier(),
                        'version' => $formData['data']['version'],
                        'prevConviction' => $formData['data']['question'],
                        'convictionsConfirmation' => $formData['convictionsConfirmation']['convictionsConfirmation'] ?? null,
                    ]
                );

                /** @var \Common\Service\Cqrs\Response $response */
                $response = $this->handleCommand($dto);

                if ($response->isOk()) {
                    if ($crudAction !== null) {
                        return $this->handleCrudAction($crudAction);
                    }

                    return $this->completeSection('convictions_penalties');
                }

                if ($response->isClientError() || $response->isServerError()) {
                    $this->flashMessengerHelper->addErrorMessage('unknown-error');
                }
            }
        }

        $this->scriptFactory->loadFiles(['lva-crud', 'convictions-penalties']);

        return $this->render('lva-convictions_penalties', $form);
    }

    /**
     * Get the correctly formatted data to populate the convictions form
     *
     * @param array $data data
     *
     * @return array
     */
    protected function getFormData($data)
    {
        return [
            'data' => [
                'version' => $data['version'],
                'question' => $data['prevConviction']
            ],
            'convictionsConfirmation' => [
                'convictionsConfirmation' => $data['convictionsConfirmation']
            ]
        ];
    }

    /**
     * Get the form used to input convictions
     *
     * @param array $data   data
     * @param array $params params
     *
     * @return mixed
     */
    protected function getConvictionsPenaltiesForm($data, $params = [])
    {
        $formHelper = $this->formHelper;

        $form = $this->formServiceManager
            ->get('lva-' . $this->lva . '-' . $this->section)
            ->getForm($params);

        $formHelper->populateFormTable(
            $form->get('data')->get('table'),
            $this->getConvictionsPenaltiesTable($data),
            'data[table]'
        );

        return $form;
    }

    /**
     * Get the data table used to display convictions
     *
     * @param array $data data
     *
     * @return mixed
     */
    protected function getConvictionsPenaltiesTable($data)
    {
        return $this->tableFactory
            ->prepareTable('lva-convictions-penalties', $data['previousConvictions']);
    }

    /**
     * Add a conviction
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function addAction()
    {
        return $this->addOrEdit('add');
    }

    /**
     * Edit a conviction
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function editAction()
    {
        return $this->addOrEdit('edit');
    }

    /**
     * handle post to edit or add conviction
     *
     * @param string $mode mode
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    protected function addOrEdit($mode)
    {
        $request = $this->getRequest();
        $data = [];

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } elseif ($mode === 'edit') {
            $id = $this->params('child_id');

            $response = $this->handleQuery(PreviousConviction::create(['id' => $id]));

            $data = ['data' => $response->getResult()];
        }

        $form = $this->getPreviousConvictionForm()->setData($data);

        if ($mode === 'edit') {
            $form->get('form-actions')->remove('addAnother');
        }

        if ($request->isPost() && $form->isValid()) {
            $dtoData = $form->getData()['data'];
            $dtoData['application'] = $this->getApplicationId();

            if ($mode === 'edit') {
                $dto = UpdatePreviousConviction::create(
                    array_merge($dtoData, ['id' => $this->params('child_id')])
                );
            } else {
                $dto = CreatePreviousConviction::create($dtoData);
            }

            /** @var \Common\Service\Cqrs\Response $response */
            $response = $this->handleCommand($dto);

            if ($response->isOk()) {
                return $this->handlePostSave(null, false);
            }

            if ($response->isClientError() || $response->isServerError()) {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }
        }

        return $this->render($mode . '_convictions_penalties', $form);
    }

    /**
     * Delete a conviction
     *
     * @return void
     */
    protected function delete()
    {
        $dto = DeletePreviousConviction::create(
            [
                'ids' => explode(',', $this->params('child_id'))
            ]
        );

        $this->handleCommand($dto);
    }

    /**
     * Get delete modal title
     *
     * @return string
     */
    protected function getDeleteTitle()
    {
        return 'delete-conviction-penalty';
    }

    /**
     * Get the previous conviction input form
     *
     * @return mixed
     */
    protected function getPreviousConvictionForm()
    {
        return $this->formHelper
            ->createFormWithRequest('Lva\PreviousConviction', $this->getRequest());
    }
}
