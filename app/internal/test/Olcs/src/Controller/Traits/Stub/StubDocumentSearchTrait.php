<?php

namespace OlcsTest\Controller\Traits\Stub;

/**
 * Stub for testing @see \Olcs\Controller\Traits\DocumentSearchTrait
 */
class StubDocumentSearchTrait
{
    use \Olcs\Controller\Traits\DocumentSearchTrait;

    public $request;

    protected function getDocumentTableName()
    {
        return 'DocTableName';
    }

    public function traitUpdateSelectValueOptions($el, $options)
    {
        $this->updateSelectValueOptions($el, $options);
    }

    public function traitMapDocumentFilters(array $extra)
    {
        return $this->mapDocumentFilters($extra);
    }

    public function getRequest()
    {
        return $this->request;
    }
}
