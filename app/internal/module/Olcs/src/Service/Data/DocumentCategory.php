<?php

namespace Olcs\Service\Data;

/**
 * Class DocumentCategory
 *
 * @package Olcs\Service\Data
 */
class DocumentCategory extends Category
{
    /**
     * constructor
     */
    public function __construct()
    {
        $this->setCategoryType(self::TYPE_IS_DOC);
    }
}
