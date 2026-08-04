<?php

namespace Common\Controller\Lva;

use Common\FeatureToggle;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableBuilder as Table;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateTrailers;
use Dvsa\Olcs\Transfer\Command\Trailer\CreateTrailer;
use Dvsa\Olcs\Transfer\Command\Trailer\DeleteTrailer;
use Dvsa\Olcs\Transfer\Command\Trailer\UpdateTrailer;
use Dvsa\Olcs\Transfer\Query\Licence\Trailers;
use Dvsa\Olcs\Transfer\Query\Trailer\Trailer;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use Laminas\Stdlib\RequestInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Trailers Controller
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
abstract class AbstractTrailersController extends AbstractController
{
    /**
     * Trait to support a CRUD table.
     */
    use Traits\CrudTableTrait;

    /**
     * The section identifier.
     *
     * @var string $section
     */
    protected $section = 'trailers';

    protected string $baseRoute = 'lva-%s/trailers';

    private $isLongerSemiTrailersFeatureToggleEnabled;

    protected FormHelperService $formHelper;

    protected FlashMessengerHelperService $flashMessengerHelper;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        protected FormServiceManager $formServiceManager,
        FlashMessengerHelperService $flashMessengerHelper,
        protected TableFactory $tableFactory,
        protected ScriptFactory $scriptFactory,
        protected DateHelperService $dateHelper,
        protected QuerySender $querySender
    ) {
        $this->formHelper = $formHelper;
        $this->flashMessengerHelper = $flashMessengerHelper;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Process Action - Index
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $response = $this->handleQuery(Trailers::create(['id' => $this->getIdentifier()]));
        if ($response->isForbidden()) {
            return $this->notFoundAction();
        }

        $result = $response->getResult();

        $data = $request->isPost() ? (array)$request->getPost() : \Common\Data\Mapper\Lva\Trailers::mapFromResult($result);

        $form = $this->getForm($request, $this->getTable($result['trailers']));
        $form->setData($data);

        if ($request->isPost()) {
            $crudAction = $this->getCrudAction([$data['table']]);
            $haveCrudAction = ($crudAction !== null);

            if ($crudAction !== null) {
                if ($this->isInternalReadOnly()) {
                    return $this->handleCrudAction($crudAction);
                }

                $this->formHelper->disableValidation($form->getInputFilter());
            }

            if ($form->isValid()) {
                $formData = (array)$form->getData();

                $cmd = UpdateTrailers::create(
                    [
                        'id' => $this->getIdentifier(),
                        'shareInfo' => $formData['trailers']['shareInfo'],
                    ]
                );

                $response = $this->handleCommand($cmd);

                if ($response->isOk()) {
                    if ($crudAction !== null) {
                        return $this->handleCrudAction($crudAction);
                    }

                    return $this->completeSection('trailers');
                }

                if ($response->isServerError()) {
                    $this->flashMessengerHelper->addUnknownError();
                }
            }
        }

        $this->scriptFactory->loadFile('lva-crud');

        $params = [
            'mainWrapperCssClass' => 'full-width',
        ];

        return $this->render('trailer', $form, $params);
    }

    /**
     * Delegating method for adding a new trailer.
     *
     * @return mixed
     */
    public function addAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        /** @var \Laminas\Form\FormInterface $form */
        $form = $this->formHelper
            ->createFormWithRequest('Lva\Trailer', $request);

        $this->alterForm($form);

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());

            if ($form->isValid()) {
                $data = $form->getData()['data'];
                $data['id'] = $this->getLicenceId();
                $data['licence'] = $this->getLicenceId();
                $data['specifiedDate'] = $this->dateHelper->getDate();
                $data['isLongerSemiTrailer'] = $this->isLongerSemiTrailersFeatureToggleEnabled()
                    ? $data['longerSemiTrailer']['isLongerSemiTrailer'] : 'N';

                $response = $this->handleCommand(CreateTrailer::create($data));

                if ($response->isOk()) {
                    return $this->handlePostSave(null, false);
                }

                $this->flashMessengerHelper->addUnknownError();
            }
        }

        return $this->render('add_trailer', $form);
    }

    /**
     * Delegating method for editing a trailer.
     *
     * @return mixed
     */
    public function editAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        /** @var \Laminas\Form\FormInterface $form */
        $form = $this->formHelper
            ->createFormWithRequest('Lva\Trailer', $request);

        $this->alterForm($form);

        $this->formHelper->remove($form, 'form-actions->addAnother');

        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData()['data'];
                $data['licence'] = $this->getLicenceId();
                $data['isLongerSemiTrailer'] = $this->isLongerSemiTrailersFeatureToggleEnabled()
                    ? $data['longerSemiTrailer']['isLongerSemiTrailer'] : 'N';

                $response = $this->handleCommand(UpdateTrailer::create($data));

                if ($response->isOk()) {
                    return $this->handlePostSave(null, false);
                }
            }
        }

        $query = Trailer::create(['id' => $this->params()->fromRoute('child_id', null)]);

        $response = $this->handleQuery($query);

        if (!$response->isOk()) {
            $this->flashMessengerHelper->addUnknownError();

            return $this->notFoundAction();
        }

        $trailer = $response->getResult();

        $data = ['data' => $trailer];
        $data['data']['longerSemiTrailer'] = [
            'isLongerSemiTrailer' => $trailer['isLongerSemiTrailer'] ? 'Y' : 'N'
        ];

        $form->setData($data);

        return $this->render('edit_trailer', $form);
    }

    /**
     * Method is called from the deleteAction in the abstract.
     *
     * @return boolean
     */
    public function delete()
    {
        $id = $this->params('child_id');
        $ids = explode(',', $id);

        $dto = DeleteTrailer::create(['ids' => $ids]);

        $response = $this->handleCommand($dto);

        return $response->isOk();
    }

    /**
     * Get delete modal title
     *
     * @return string
     */
    protected function getDeleteTitle()
    {
        return 'delete-trailers';
    }

    /**
     * Prepare and return the form with the form data.
     *
     * @param array $tableData Table data
     *
     * @return Table The trailers table.
     */
    protected function getTable($tableData)
    {
        $table = $this->tableFactory->prepareTable('lva-trailers', $tableData);

        if (!$this->isLongerSemiTrailersFeatureToggleEnabled()) {
            $table->removeColumn('isLongerSemiTrailer');
        }

        return $table;
    }

    /**
     * Return the trailer form.
     *
     * @param RequestInterface $request The request
     * @param Table            $table   The table to add to the form.
     *
     * @return \Laminas\Form\Form $form The form
     */
    protected function getForm(RequestInterface $request, $table)
    {
        return $this->formServiceManager
            ->get('lva-licence-trailers')
            ->getForm($request, $table);
    }

    /**
     * Alter form
     *
     * @param Form $form Form
     */
    private function alterForm(Form $form): void
    {
        if (!$this->isLongerSemiTrailersFeatureToggleEnabled()) {
            $this->formHelper->remove($form, 'data->longerSemiTrailer');
        }
    }

    /**
     * Is longer semi-trailers feature toggle enabled
     */
    private function isLongerSemiTrailersFeatureToggleEnabled(): bool
    {
        if ($this->isLongerSemiTrailersFeatureToggleEnabled === null) {
            $this->isLongerSemiTrailersFeatureToggleEnabled
                = $this->querySender->featuresEnabled([FeatureToggle::LONGER_SEMI_TRAILERS]);
        }

        return $this->isLongerSemiTrailersFeatureToggleEnabled;
    }
}
