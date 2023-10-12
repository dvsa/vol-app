<?php

namespace Olcs\Controller\Cases\Submission;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Submission\AssignSubmission as AssignUpdateDto;
use Dvsa\Olcs\Transfer\Command\Submission\InformationCompleteSubmission as InformationCompleteDto;
use Dvsa\Olcs\Transfer\Query\Cases\PresidingTc\GetList as TcListDto;
use Dvsa\Olcs\Transfer\Query\Submission\Submission as ItemDto;
use Laminas\Navigation\Navigation;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Data\Mapper\Submission as Mapper;
use Olcs\Form\Model\Form\SubmissionInformationComplete as CompleteForm;
use Olcs\Form\Model\Form\SubmissionSendTo as SendToForm;

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
            'options' => [
                'fragment' => 'submissionActions',
            ],
            'reUseParams' => true,
        ],
        'information-complete' => [
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

    protected $itemParams = ['id' => 'submission'];

    protected $mapperClass = Mapper::class;

    protected $inlineScripts = [
        'assignAction' => ['forms/assign-submission']
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
     * Generate form action to update submission, setting assigned_date, sender/recipient_user_ids
     *
     * @return array|\Laminas\View\Model\ViewModel
     */
    public function assignAction()
    {
        $this->formClass = SendToForm::class;
        $this->updateCommand = AssignUpdateDto::class;
        $this->editContentTitle = 'Assign submission';

        return $this->editAction();
    }

    protected function alterFormForAssign($form, $initialData)
    {
        $response = $this->handleQuery(
            TcListDto::create(
                [
                    'limit' => 200,
                    'order' => 'asc',
                    'page' => 1,
                    'sort' => 'id',
                ]
            )
        );
        if ($response->isOk()) {
            // Get list of TC user ide from Presiding TCs response
            $tcUserIds = [];
            foreach ($response->getResult()['results'] as $tcResult) {
                if (isset($tcResult['user']['id'])) {
                    $tcUserIds[] = $tcResult['user']['id'];
                }
            }

            //Get user list uses for Other User select box
            $tcUsers = $form->get('fields')->get('recipientUser')->getValueOptions();
            $recipientUsers = $tcUsers;
            foreach ($tcUsers as $groupId => $group) {
                foreach ($group['options'] as $optionUserId => $name) {
                    //remove users who are not TCs from each set of group options
                    if (!in_array($optionUserId, $tcUserIds)) {
                        unset($tcUsers[$groupId]['options'][$optionUserId]);
                    } else {
                        unset($recipientUsers[$groupId]['options'][$optionUserId]);
                    }
                }
                // If the above removal left any empty groups, remove the groups
                if (empty($tcUsers[$groupId]['options'])) {
                    unset($tcUsers[$groupId]);
                }
            }
            $form->get('fields')->get('presidingTcUser')->setValueOptions($tcUsers);
            $form->get('fields')->get('recipientUser')->setValueOptions($recipientUsers);
        } else {
            throw new \RuntimeException('Cannot retrieve TC/DTC user list');
        }
        return $form;
    }

    /**
     * Generate form action to update submission, setting information_complete_date
     *
     * @return array|\Laminas\View\Model\ViewModel
     */
    public function informationCompleteAction()
    {
        $this->formClass = CompleteForm::class;

        $this->updateCommand = InformationCompleteDto::class;
        $this->editContentTitle = 'Set info complete';

        return $this->editAction();
    }
}
