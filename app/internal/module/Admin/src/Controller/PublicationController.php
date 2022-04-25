<?php

/**
 * Publication Controller
 */

namespace Admin\Controller;

use Common\Service\Table\TableBuilder;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount;
use Olcs\Controller\AbstractInternalController;
use Dvsa\Olcs\Transfer\Query\Publication\PendingList;
use Dvsa\Olcs\Transfer\Command\Publication\Publish as PublishCmd;
use Dvsa\Olcs\Transfer\Command\Publication\Generate as GenerateCmd;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Laminas\View\Model\ViewModel;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationService;

/**
 * Publication Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PublicationController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-publication';
    protected $listVars = [];
    protected $inlineScripts = array('indexAction' => ['table-actions', 'file-link']);
    protected $listDto = PendingList::class;
    protected $tableName = 'admin-publication';
    protected $crudConfig = [
        'generate' => ['requireRows' => true],
        'publish' => ['requireRows' => true],
    ];


    /**
     * @param TableBuilder $table
     * @param  array       $data
     *
     * @return TableBuilder
     */
    protected function alterTable($table, $data): TableBuilder
    {
        $data = $this->getPublicationLinkData($data);
        $table->loadData($data);
        return $table;
    }

    /**
     * @param $data
     * @return array
     */
    protected function getPublicationLinkData($data)
    {
        $webDavJsonWebTokenGenerationService = $this->getServiceLocator()->get(WebDavJsonWebTokenGenerationService::class);
        $loginId = $this->currentUser()->getIdentity()->getUsername();

        foreach ($data['results'] as $result => $value) {
            if (isset($value['document'])) {
                $jwt = $webDavJsonWebTokenGenerationService->generateToken(
                    $loginId,
                    $value['document']['identifier']
                );
                $url = $webDavJsonWebTokenGenerationService->getJwtWebDavLink(
                    $jwt,
                    $value['document']['identifier'],
                );
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
