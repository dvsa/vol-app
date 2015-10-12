<?php
/**
 * Submission Decision Controller
 */
namespace Olcs\Controller\Cases\Submission;

use Dvsa\Olcs\Transfer\Command\Submission\AssignSubmission as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Submission\Submission as ItemDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Data\Mapper\Submission as Mapper;
use Olcs\Form\Model\Form\SubmissionSendTo as Form;

/**
 * Process Submission Controller
 */
class ProcessSubmissionController extends AbstractInternalController implements CaseControllerInterface
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_submissions';

    /**
     * @var array
     */
    protected $redirectConfig = [
        'assign' => [
            'route' => 'submission',
            'action' => 'details',
            'reUseParams' => true,
        ]
    ];

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $itemDto = ItemDto::class;

    protected $itemParams = ['id' => 'submission'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $editContentTitle = 'Assign submission';

    public function assignAction()
    {
        return $this->editAction();
    }
}
