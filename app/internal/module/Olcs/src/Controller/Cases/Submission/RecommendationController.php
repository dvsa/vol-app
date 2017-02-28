<?php

namespace Olcs\Controller\Cases\Submission;

use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionAction as CreateDto;
use Dvsa\Olcs\Transfer\Command\Submission\UpdateSubmissionAction as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Submission\SubmissionAction as ItemDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Data\Mapper\SubmissionAction as Mapper;
use Olcs\Form\Model\Form\SubmissionRecommendation as Form;
use Zend\Form\FormInterface;

/**
 * Submission Recommendation Controller
 */
class RecommendationController extends AbstractInternalController implements CaseControllerInterface
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
    protected $scriptFiles = ['tinymce/jquery.tinymce.min.js'];

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
    protected $addContentTitle = 'Add submission recommendation';
    protected $editContentTitle = 'Edit submission recommendation';

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
        'isDecision' => 'N',
    ];

    /**
     * Process action - Index
     *
     * @return \Zend\View\Model\ConsoleModel|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return $this->notFoundAction();
    }

    /**
     * Process action - Details
     *
     * @return \Zend\View\Model\ConsoleModel|\Zend\View\Model\ViewModel
     */
    public function detailsAction()
    {
        return $this->notFoundAction();
    }

    /**
     * Process action - Delete
     *
     * @return \Zend\View\Model\ConsoleModel|\Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        return $this->notFoundAction();
    }

    /**
     * Alter form for Add
     *
     * @param FormInterface $form     Form
     * @param array         $formData Form Data
     *
     * @return FormInterface
     */
    protected function alterFormForAdd(FormInterface $form, $formData)
    {
        return $this->alterForm($form, $formData['id']);
    }

    /**
     * Alter form for Edit
     *
     * @param FormInterface $form     Form
     * @param array         $formData Form Data
     *
     * @return FormInterface
     */
    protected function alterFormForEdit(FormInterface $form, $formData)
    {
        return $this->alterForm($form, $formData['fields']['id']);
    }

    /**
     * Change the id of the text area to be unique (avoid DOM clashes with multiple TinyMCE instances
     *
     * Alter form for Add
     *
     * @param FormInterface $form Form
     * @param int           $id   Identifier
     *
     * @return FormInterface
     */
    private function alterForm(FormInterface $form, $id)
    {
        $form->get('fields')->get('comment')->setAttribute('id', $id . time());
        return $form;
    }
}
