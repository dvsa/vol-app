<?php

namespace Olcs\Controller\Licence\Processing;

use Dvsa\Olcs\Transfer\Command\Publication\DeletePublicationLink;
use Dvsa\Olcs\Transfer\Command\Publication\UpdatePublicationLink;
use Dvsa\Olcs\Transfer\Query\Publication\PublicationLink as PublicationLinkDto;
use Dvsa\Olcs\Transfer\Query\Publication\PublicationLinkList;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Data\Mapper\PublicationLink as PublicationLinkMapper;
use Olcs\Form\Model\Form\Publication as PublicationForm;
use Olcs\Form\Model\Form\PublicationNotNew as PublicationNotNewForm;

class LicenceProcessingPublicationsController extends AbstractInternalController implements
    LicenceControllerInterface,
    LeftViewProvider
{
    protected $navigationId = 'licence_processing_publications';
    protected $defaultTableSortField = 'createdOn';
    protected $tableName = 'publication';
    protected $listDto = PublicationLinkList::class;
    protected $itemDto = PublicationLinkDto::class;
    protected $listVars = ['licence'];
    protected $mapperClass = PublicationLinkMapper::class;
    protected $formClass = PublicationForm::class;
    protected $updateCommand = UpdatePublicationLink::class;
    protected $deleteCommand = DeletePublicationLink::class;
    protected $inlineScripts = ['indexAction' => ['table-actions']];
    protected $addContentTitle = 'Add publication';
    protected $editContentTitle = 'Edit publication';

    /**
     * get mothod left View
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/processing/partials/left');

        return $view;
    }

    /**
     * edit action
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $publicationLink = $this->getPublicationLink();

        //if publication status is not new, switch the form
        if (!$publicationLink['isNew']) {
            $this->formClass = PublicationNotNewForm::class;
        }

        return parent::editAction();
    }

    /**
     * Gets PublicationLink information
     *
     * @return array|mixed
     */
    private function getPublicationLink()
    {
        $params = ['id' => $this->params()->fromRoute('id')];
        $response = $this->handleQuery(PublicationLinkDto::create($params));

        if ($response->isServerError() || $response->isClientError()) {
            $this->flashMessengerHelperService->addErrorMessage('unknown-error');
        }

        return $response->getResult();
    }
}
