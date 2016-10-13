<?php

namespace OlcsTest\Controller\Traits\Stub;

/**
 * Stub for testing @see \Olcs\Controller\Traits\TaskSearchTrait
 */
class StubTaskSearchTrait
{
    use \Olcs\Controller\Traits\TaskSearchTrait;

    public $request;
    public $currentUser;

    public function traitUpdateSelectValueOptions($el, $options)
    {
        $this->updateSelectValueOptions($el, $options);
    }

    public function traitMapTaskFilters($extra)
    {
        return $this->mapTaskFilters($extra);
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function currentUser()
    {
        return $this->currentUser;
    }
}
