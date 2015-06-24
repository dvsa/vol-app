<?php

/**
 * Operator Irfo Details Controller
 */
namespace Olcs\Controller\Operator;

use Dvsa\Olcs\Transfer\Command\Irfo\UpdateIrfoDetails as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoDetails as ItemDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\OperatorControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Data\Mapper\IrfoDetails as Mapper;
use Olcs\Form\Model\Form\IrfoDetails as Form;

/**
 * Operator Irfo Details Controller
 */
class OperatorIrfoDetailsController extends AbstractInternalController implements
    OperatorControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'operator_irfo_details';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'editAction' => ['trading-names', 'irfo-partners']
    ];

    public function getPageLayout()
    {
        return 'layout/operator-section';
    }

    public function getPageInnerLayout()
    {
        return 'layout/operator-subsection';
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $itemDto = ItemDto::class;
    protected $itemParams = ['id' => 'organisation'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;

    public function indexAction()
    {
        return $this->redirectToIndex();
    }

    public function detailsAction()
    {
        return $this->notFoundAction();
    }

    public function addAction()
    {
        return $this->notFoundAction();
    }

    public function deleteAction()
    {
        return $this->notFoundAction();
    }

    public function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(
            'operator/irfo/details',
            ['action' => 'edit'],
            ['code' => '303'],
            true
        );
    }
}
