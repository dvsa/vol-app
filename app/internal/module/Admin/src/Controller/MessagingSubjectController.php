<?php

namespace Admin\Controller;

use Admin\Data\Mapper\MessagingSubject as MessagingSubjectMapper;
use Admin\Form\Model\Form\MessagingSubject;
use Common\Form\Form;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Messaging\Subject\Create as CreateCmd;
use Dvsa\Olcs\Transfer\Command\Messaging\Subject\Delete as DeleteCmd;
use Dvsa\Olcs\Transfer\Command\Messaging\Subject\Update as UpdateCmd;
use Dvsa\Olcs\Transfer\Query\Messaging\Subjects\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\Messaging\Subjects\GetList as ListDto;
use Laminas\Navigation\Navigation;
use Olcs\Controller\AbstractInternalController;
use Olcs\Service\Data\SubCategory;

class MessagingSubjectController extends AbstractInternalController
{
    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
        'addAction' => ['forms/selected-category-subcategory-filtering'],
        'editAction' => ['forms/selected-category-subcategory-filtering'],
    ];

    protected $tableName = 'admin-messaging-subjects';
    protected $defaultTableSortField = 'description';
    protected $defaultTableOrderField = 'ASC';

    protected $deleteCommand = DeleteCmd::class;
    protected $createCommand = CreateCmd::class;
    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;
    protected $updateCommand = UpdateCmd::class;

    protected $navigationId = 'admin-dashboard/message-categories';

    protected $formClass = MessagingSubject::class;

    protected $mapperClass = MessagingSubjectMapper::class;

    protected $addContentTitle = 'Add message category';
    protected $editContentTitle = 'Edit message category';

    public function __construct(
        TranslationHelperService $translationHelperService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelperService,
        Navigation $navigation,
        protected SubCategory $subCategoryDataService
    ) {
        parent::__construct($translationHelperService, $formHelper, $flashMessengerHelperService, $navigation);
    }

    /**
     * Only show the sub-categories of the saved category when editing
     */
    public function alterFormForEdit(Form $form, $formData)
    {
        if (isset($formData['details']['category'])) {
            $this->subCategoryDataService->setCategory($formData['details']['category']);
        }

        return $form;
    }
}
