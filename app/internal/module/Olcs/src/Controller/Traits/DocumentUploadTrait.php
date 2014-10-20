<?php

namespace Olcs\Controller\Traits;

/**
 * Class DocumentUploadTrait
 * @package Olcs\Controller
 */
trait DocumentUploadTrait
{
    private function getDefaultCategory($categories)
    {
        $name = $this->categoryMap[$this->params('type')];
        return array_search($name, $categories);
    }

    protected function getListData(
        $entity,
        $filters = array(),
        $titleField = '',
        $keyField = '',
        $showAll = self::EMPTY_LABEL
    ) {
        return parent::getListData($entity, $filters, 'description', 'id', $showAll);
    }
}
