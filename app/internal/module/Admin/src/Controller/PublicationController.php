<?php

namespace Admin\Controller;

use Common\Auth\Service\RefreshTokenService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableBuilder;
use Dvsa\Olcs\Transfer\Command\Publication\Generate as GenerateCmd;
use Dvsa\Olcs\Transfer\Command\Publication\Publish as PublishCmd;
use Dvsa\Olcs\Transfer\Query\Publication\PendingList;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits\WebDavSessionTrait;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationService;

class PublicationController extends AbstractInternalController implements LeftViewProvider
{
    use WebDavSessionTrait;

    protected $navigationId = 'admin-dashboard/admin-publication';
    protected $listVars = [];
    protected $inlineScripts = ['indexAction' => ['table-actions', 'file-link']];
    protected $listDto = PendingList::class;
    protected $tableName = 'admin-publication';
    protected $crudConfig = [
        'generate' => ['requireRows' => true],
        'publish' => ['requireRows' => true],
    ];

    public function __construct(
        TranslationHelperService $translationHelperService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelperService,
        Navigation $navigation,
        protected WebDavJsonWebTokenGenerationService $webDavJsonWebTokenGenerationService,
        private readonly ?\Redis $redis,
        private readonly RefreshTokenService $refreshTokenService,
    ) {
        parent::__construct($translationHelperService, $formHelper, $flashMessengerHelperService, $navigation);
    }
    /**
     * @param TableBuilder $table
     * @param array        $data
     *
     * @return TableBuilder
     */
    #[\Override]
    protected function alterTable($table, $data): TableBuilder
    {
        $data = $this->getPublicationLinkData($data);
        $table->loadData($data);
        return $table;
    }

    /**
     * @param  $data
     * @return array
     */
    protected function getPublicationLinkData($data)
    {
        $webDavEnabled = $this->isInternalWebDavEnabled();

        if ($webDavEnabled) {
            $this->refreshCognitoTokenForWebDav();
        }

        foreach ($data['results'] as $result => $value) {
            if (isset($value['document'])) {
                $identifier = $value['document']['identifier'];
                $documentId = (int) ($value['document']['id'] ?? 0);

                $documentSize = (int) ($value['document']['size'] ?? 0);
                $url = $this->generateWebDavUrl($identifier, $documentId, $webDavEnabled, $documentSize);
                $data['results'][$result]['webDavUrl'] = $url;
            }
        }
        return $data;
    }

    /**
     * Specifically for navigation. For jumping us into the pending.
     *
     * @return \Laminas\Http\Response
     */
    public function jumpAction()
    {
        return $this->redirect()->toRoute(
            'admin-dashboard/admin-publication/pending',
            [],
            ['code' => 303]
        );
    }

    #[\Override]
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-publication',
                'navigationTitle' => 'Publications'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Generate action
     *
     * @return mixed|\Laminas\Http\Response
     */
    public function generateAction()
    {
        return $this->processCommand(
            new GenericItem(['id' => 'id']),
            GenerateCmd::class,
            'Publication was generated, a new publication was also created'
        );
    }

    /**
     * Publish action
     *
     * @return mixed|\Laminas\Http\Response
     */
    public function publishAction()
    {
        return $this->processCommand(new GenericItem(['id' => 'id']), PublishCmd::class);
    }
}
