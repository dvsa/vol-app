<?php

/**
 * SubmissionSectionComment Controller
 */
namespace Olcs\Controller\Cases\Submission;

use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionSectionComment as CreateDto;
use Dvsa\Olcs\Transfer\Command\Submission\UpdateSubmissionSectionComment as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Submission\SubmissionSectionComment as ItemDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Data\Mapper\SubmissionSectionComment as Mapper;
use Olcs\Form\Model\Form\SubmissionSectionComment as Form;

/**
 * Submission Section Comment Controller
 */
class SubmissionSectionCommentController extends AbstractInternalController implements CaseControllerInterface
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
        'add' => [
            'route' => 'submission',
            'action' => 'details',
            'reUseParams' => true,
            'resultIdMap' => [
                'section' => 'submissionSection'
            ]
        ],
        'edit' => [
            'route' => 'submission',
            'action' => 'details',
            'reUseParams' => true,
            'resultIdMap' => [
                'section' => 'submissionSection'
            ]
        ]
    ];

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $itemDto = ItemDto::class;

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add comment';
    protected $editContentTitle = 'Edit comment';

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
        'submission' => 'route',
        'submissionSection' => 'route',
    ];
}
