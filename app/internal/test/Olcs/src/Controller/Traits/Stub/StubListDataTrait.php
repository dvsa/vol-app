<?php

namespace OlcsTest\Controller\Traits\Stub;

/**
 * Stub ListDataTrait
 */
class StubListDataTrait
{
    use \Olcs\Controller\Traits\ListDataTrait;

    private $handleQueryResponse;

    public function setHandleQueryResponse($response)
    {
        $this->handleQueryResponse = $response;
    }

    protected function handleQuery()
    {
        return $this->handleQueryResponse;
    }
}
