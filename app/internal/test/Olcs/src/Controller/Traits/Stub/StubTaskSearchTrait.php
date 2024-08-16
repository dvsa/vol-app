<?php

namespace OlcsTest\Controller\Traits\Stub;

use Common\Service\Helper\FormHelperService;
use Olcs\Service\Data\SubCategory;

/**
 * Stub for testing @see \Olcs\Controller\Traits\TaskSearchTrait
 */
class StubTaskSearchTrait
{
    use \Olcs\Controller\Traits\TaskSearchTrait;

    public $currentUser;

    protected FormHelperService $formHelper;
    protected SubCategory $subCategoryDataService;

    public function __construct(FormHelperService $formHelper, SubCategory $subCategoryDataService)
    {
        $this->formHelper = $formHelper;
        $this->subCategoryDataService = $subCategoryDataService;
    }

    public function traitUpdateSelectValueOptions($el, $options)
    {
        $this->updateSelectValueOptions($el, $options);
    }

    public function traitMapTaskFilters($extra)
    {
        return $this->mapTaskFilters($extra);
    }

    public function traitGetTaskForm($filters)
    {
        return $this->getTaskForm($filters);
    }

    public function currentUser()
    {
        return $this->currentUser;
    }

    public function traitProcessTasksActions($type)
    {
        return $this->processTasksActions($type);
    }
}
