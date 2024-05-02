<?php

namespace Olcs\Controller\TransportManager\Details;

use Common\Controller\Traits\GenericUpload;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\TransportManagerHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\TransportManager\TransportManagerController;

abstract class AbstractTransportManagerDetailsController extends TransportManagerController
{
    use GenericUpload;

    protected FileUploadHelperService $uploadHelper;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        FlashMessengerHelperService $flashMessengerHelper,
        TranslationHelperService $translationHelper,
        $navigation,
        protected TransportManagerHelperService $transportManagerHelper,
        FileUploadHelperService $uploadHelper
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $flashMessengerHelper,
            $translationHelper,
            $navigation
        );
        $this->uploadHelper = $uploadHelper;
    }

    /**
     * Redirect to index
     *
     * @return \Laminas\Http\Response
     */
    public function redirectToIndex()
    {
        $tm = $this->getFromRoute('transportManager');
        $routeParams = ['transportManager' => $tm];
        return $this->redirect()->toRouteAjax(null, $routeParams);
    }

    /**
     * Delete record or multiple records
     *
     * @param  string $command DTO class name
     * @return mixed
     */
    protected function deleteRecordsCommand($command)
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }
        $translator = $this->translationHelper;
        $id = $this->getFromRoute('id');
        if (!$id) {
            // multiple delete
            $id = $this->params()->fromQuery('id');
        }

        if (is_string($id) && strstr($id, ',')) {
            $id = explode(',', $id);
        }

        $response = $this->confirm(
            $translator->translate('transport-manager.previous-history.delete-question')
        );

        if ($response instanceof ViewModel) {
            return $this->renderView($response);
        }

        $ids = !is_array($id) ? [$id] : $id;
        $commandResponse = $this->handleCommand($command::create(['ids' => $ids]));
        if (!$commandResponse->isOk()) {
            throw new \RuntimeException('Error deleting ' . $command);
        }

        $this->addSuccessMessage('generic.deleted.success');

        return $this->redirectToIndex();
    }
}
