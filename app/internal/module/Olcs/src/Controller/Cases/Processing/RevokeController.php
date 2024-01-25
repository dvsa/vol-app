<?php

namespace Olcs\Controller\Cases\Processing;

use Dvsa\Olcs\Transfer\Command\Cases\ProposeToRevoke\CreateProposeToRevoke as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\ProposeToRevoke\UpdateProposeToRevoke as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Cases\ProposeToRevoke\ProposeToRevokeByCase as ItemDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\Revoke as Mapper;
use Olcs\Form\Model\Form\Revoke as Form;

class RevokeController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_processing_in_office_revocation';

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'sections/cases/pages/in-office-revocation';
    protected $detailsViewPlaceholderName = 'proposeToRevoke';
    protected $itemDto = ItemDto::class;
    // 'id' => 'conviction', to => from
    protected $itemParams = ['case'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add in-office revocation';
    protected $editContentTitle = 'Edit in-office revocation';

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $createCommand = CreateDto::class;

    /**
     * Form data for the add form.
     *
     * Format is name => value
     * name => "route" means get value from route,
     * see conviction controller
     *
     * @var array
     */
    protected $defaultData = [
        'case' => 'route',
    ];

    /**
     * Lock unused actions
     *
     * @return \Laminas\Http\Response
     */
    public function indexAction()
    {
        return $this->redirectToIndex();
    }

    /**
     * Lock unused actions
     *
     * @return array
     */
    public function deleteAction()
    {
        return $this->notFoundAction();
    }

    /**
     * Redirect to index
     *
     * @return \Laminas\Http\Response
     */
    public function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(
            null,
            ['action' => 'details'],
            ['code' => '303'],
            true
        );
    }

    /**
     * Set left part
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/processing/partials/left');

        return $view;
    }
}
