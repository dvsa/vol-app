<?php

/**
 * Operator Irfo Details Controller
 */
namespace Olcs\Controller\Operator;

use Dvsa\Olcs\Transfer\Command\Irfo\UpdateIrfoDetails as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoDetails as ItemDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\OperatorControllerInterface;
use Olcs\Data\Mapper\IrfoDetails as Mapper;
use Olcs\Form\Model\Form\IrfoDetails as Form;
use Zend\View\Model\ViewModel;

/**
 * Operator Irfo Details Controller
 */
class OperatorIrfoDetailsController extends AbstractInternalController implements
    OperatorControllerInterface,
    LeftViewProvider
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
        'editAction' => []
    ];

    protected $redirectConfig = [
        'index' => [
            'route' => 'operator/irfo/details'
        ],
        'edit' => [
            'action' => 'edit'
        ]
    ];

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/operator/partials/left');

        return $view;
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
    protected $editContentTitle = 'IRFO Details';

    public function indexAction()
    {
        return $this->redirectTo([]);
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
}
