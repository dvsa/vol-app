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
use Olcs\Form\Model\Form\SubmissionSectionAddComment as AddForm;
use Olcs\Form\Model\Form\SubmissionSectionEditComment as EditForm;
use \Laminas\Form\Form as LaminasForm;

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
    protected $formClass = AddForm::class;
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

    /**
     * Swaps the default add form for the edit form
     *
     * @return array|\Laminas\View\Model\ViewModel
     */
    public function editAction()
    {
        $this->formClass = EditForm::class;

        return parent::editAction();
    }

    /**
     * Alters the edit form at runtime
     *
     * @param LaminasForm $form     the form
     * @param array    $formData form data
     *
     * @return LaminasForm
     */
    protected function alterFormForEdit(LaminasForm $form, $formData)
    {
        return $this->alterForm($form, $formData['fields']['id']);
    }

    /**
     * Change the id of the text area to be unique (avoid DOM clashes with multiple TinyMCE instances
     *
     * @param LaminasForm $form the form
     * @param int      $id   the id
     *
     * @return LaminasForm
     */
    private function alterForm(LaminasForm $form, $id)
    {
        $form->get('fields')->get('comment')->setAttribute('id', $id);
        return $form;
    }
}
