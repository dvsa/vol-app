<?php

namespace Olcs\Controller\Cases\Submission;

use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionAction as CreateDto;
use Dvsa\Olcs\Transfer\Command\Submission\UpdateSubmissionAction as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Submission\SubmissionAction as ItemDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Data\Mapper\SubmissionAction as Mapper;
use Olcs\Form\Model\Form\SubmissionDecision as Form;
use Zend\Form\Form as ZendForm;


/**
 * Submission Decision Controller
 */
class DecisionController extends AbstractInternalController implements CaseControllerInterface
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
    protected $inlineScripts = [
        'addAction' => ['forms/submission-recommendation-decision'],
        'editAction' => ['forms/submission-recommendation-decision'],
    ];

    /**
     * @var array
     */
    protected $redirectConfig = [
        'add' => [
            'route' => 'submission',
            'action' => 'details',
            'options' => [
                'fragment' => 'submissionActions',
            ],
            'reUseParams' => true,
        ],
        'edit' => [
            'route' => 'submission',
            'action' => 'details',
            'options' => [
                'fragment' => 'submissionActions',
            ],
            'reUseParams' => true,
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
    protected $addContentTitle = 'Add submission decision';
    protected $editContentTitle = 'Edit submission decision';

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
        'isDecision' => 'Y',
    ];

    /**
     * index Action
     *
     * @return Zend/Http/Response
     */
    public function indexAction()
    {
        return $this->notFoundAction();
    }

    /**
     * details action
     *
     * @return Zend/Http/Response
     */
    public function detailsAction()
    {
        return $this->notFoundAction();
    }

    /**
     * delete Action
     *
     * @return Zend/Http/Response
     */
    public function deleteAction()
    {
        return $this->notFoundAction();
    }

    /**
     * Alter form for Add
     *
     * @param ZendForm $form     form
     * @param array    $formData formData
     *
     * @return ZendForm|array
     */
    protected function alterFormForAdd(ZendForm $form, $formData)
    {
        return $this->alterForm($form, !empty($formData['id']) ? $formData['id'] : '');
    }

    /**
     *alter Form for Edit
     *
     * @param ZendForm $form     form
     * @param array    $formData formData
     *
     * @return ZendForm|array
     */
    protected function alterFormForEdit(ZendForm $form, $formData)
    {
        return $this->alterForm($form, $formData['fields']['id']);
    }

    /**
     * Change the id of the text area to be unique (avoid DOM clashes with multiple TinyMCE instances)
     *
     * @param ZendForm $form form
     * @param int      $id   id
     *
     * @return ZendForm
     */
    private function alterForm(ZendForm $form)
    {
        $form->get('fields')->get('comment')->setAttribute('id', $id . time());
        return $form;
    }
}
