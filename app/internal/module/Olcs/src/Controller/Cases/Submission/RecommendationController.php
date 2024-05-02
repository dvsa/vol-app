<?php

namespace Olcs\Controller\Cases\Submission;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionAction as CreateDto;
use Dvsa\Olcs\Transfer\Command\Submission\UpdateSubmissionAction as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Submission\SubmissionAction as ItemDto;
use Laminas\Form\FormInterface;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Data\Mapper\SubmissionAction as Mapper;
use Olcs\Form\Model\Form\SubmissionRecommendation as Form;

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


    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessenger,
        Navigation $navigation
    ) {
        parent::__construct($translationHelper, $formHelper, $flashMessenger, $navigation);
    }
    /**
     * Process action - Index
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        return $this->notFoundAction();
    }

    /**
     * Process action - Details
     *
     * @return ViewModel
     */
    public function detailsAction()
    {
        return $this->notFoundAction();
    }

    /**
     * Process action - Delete
     *
     * @return ViewModel
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
        return $this->alterForm($form, !empty($formData['id']) ? $formData['id'] : '');
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
