<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Traits\Stub;

/**
 * Stub ListDataTrait
 */
class StubListDataTrait
{
    use \Olcs\Controller\Traits\ListDataTrait;

    private $handleQueryResponse;

    public function setHandleQueryResponse(mixed $response): void
    {
        $this->handleQueryResponse = $response;
    }

    protected function handleQuery(): mixed
    {
        return $this->handleQueryResponse;
    }
}
