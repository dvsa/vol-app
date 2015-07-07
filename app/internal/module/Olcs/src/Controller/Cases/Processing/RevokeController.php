<?php

/**
 * Revoke Controller
 */
namespace Olcs\Controller\Cases\Processing;

use Dvsa\Olcs\Transfer\Command\Cases\ProposeToRevoke\CreateProposeToRevoke as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\ProposeToRevoke\UpdateProposeToRevoke as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Cases\ProposeToRevoke\ProposeToRevokeByCase as ItemDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Data\Mapper\Revoke as Mapper;
use Olcs\Form\Model\Form\Revoke as Form;

/**
 * Revoke Controller
 */
class RevokeController extends AbstractInternalController implements
    CaseControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_processing_in_office_revocation';

    public function getPageLayout()
    {
        return 'layout/case-section';
    }

    public function getPageInnerLayout()
    {
        return 'layout/case-details-subsection';
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'pages/case/in-office-revocation';
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

    public function indexAction()
    {
        return $this->redirectToIndex();
    }

    public function deleteAction()
    {
        return $this->notFoundAction();
    }

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
     * Not found is a valid response for this particular controller
     */
    public function notFoundAction()
    {
        return $this->viewBuilder()->buildViewFromTemplate($this->detailsViewTemplate);
    }
}
