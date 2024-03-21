<?php

namespace Olcs\Controller\TransportManager;

use Common\Form\Form;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Cases\CreateCase as CreateCaseCommand;
use Dvsa\Olcs\Transfer\Command\Cases\DeleteCase as DeleteCaseCommand;
use Dvsa\Olcs\Transfer\Command\Cases\UpdateCase as UpdateCaseCommand;
use Dvsa\Olcs\Transfer\Query\Cases\ByTransportManager as CasesByTmDto;
use Dvsa\Olcs\Transfer\Query\Cases\Cases as CasesDto;
use Laminas\Navigation\Navigation;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;
use Olcs\Data\Mapper\GenericFields as GenericMapper;
use Olcs\Form\Model\Form\Cases as CaseForm;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;

class TransportManagerCaseController extends AbstractInternalController implements
    TransportManagerControllerInterface
{
    protected $navigationId = 'transport_manager_cases';
    protected $listDto = CasesByTmDto::class;
    protected $itemDto = CasesDto::class;

    protected $defaultData = ['transportManager' => AddFormDefaultData::FROM_ROUTE];
    protected $listVars = ['transportManager'];
    protected $itemParams = ['id'];
    protected $formClass = CaseForm::class;
    protected $createCommand = CreateCaseCommand::class;
    protected $updateCommand = UpdateCaseCommand::class;
    protected $deleteCommand = DeleteCaseCommand::class;
    protected $mapperClass = GenericMapper::class;
    protected $tableName = 'cases';
    protected $addContentTitle = 'Add case';
    protected $editContentTitle = 'Edit case';

    protected $inlineScripts = ['indexAction' => ['table-actions']];

    protected $redirectConfig = [
        'add' => [
            'route' => 'case',
            'action' => 'details',
            'resultIdMap' => [
                'case' => 'case'
            ],
            'reUseParams' => false
        ],
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
     * Alter Form to remove case type options depending on where the case was added from.
     *
     * @param  Form $form
     * @param  array $initialData
     * @return Form
     */
    public function alterFormForAdd($form, $initialData)
    {
        return $this->getFormCaseTypes($form);
    }

    /**
     * Alter Form to remove case type options depending on where the case was added from.
     *
     * @param  Form $form
     * @param  array                   $initialData
     * @return Form
     */
    public function alterFormForEdit($form, $initialData)
    {
        return $this->getFormCaseTypes($form);
    }

    /**
     * Works out the case types
     *
     * @param  Form $form
     * @return Form
     */
    private function getFormCaseTypes($form)
    {
        $unwantedOptions = ['case_t_app' => '', 'case_t_lic' => '', 'case_t_imp' => ''];

        $options = $form->get('fields')
            ->get('caseType')
            ->getValueOptions();

        $form->get('fields')
            ->get('caseType')
            ->setValueOptions(array_diff_key($options, $unwantedOptions))
            ->setEmptyOption(null);

        return $form;
    }
}
