<?php

namespace OlcsTest\Controller\Traits\Stub;

/**
 * Stub for testing @see \Olcs\Controller\Traits\TaskSearchTrait
 */
class StubTaskSearchTrait
{
    use \Olcs\Controller\Traits\TaskSearchTrait;

    public $currentUser;

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
}
