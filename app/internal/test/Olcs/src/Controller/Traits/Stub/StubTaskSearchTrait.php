<?php

declare(strict_types=1);

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

    public function __construct(protected FormHelperService $formHelper, protected SubCategory $subCategoryDataService)
    {
    }

    public function traitUpdateSelectValueOptions(mixed $el, mixed $options): void
    {
        $this->updateSelectValueOptions($el, $options);
    }

    public function traitMapTaskFilters(mixed $extra): array
    {
        return $this->mapTaskFilters($extra);
    }

    public function traitGetTaskForm(mixed $filters): mixed
    {
        return $this->getTaskForm($filters);
    }

    public function currentUser(): mixed
    {
        return $this->currentUser;
    }

    public function traitProcessTasksActions(mixed $type): mixed
    {
        return $this->processTasksActions($type);
    }
}
