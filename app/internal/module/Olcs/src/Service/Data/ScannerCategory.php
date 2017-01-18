<?php

namespace Olcs\Service\Data;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class ScannerCategory extends Category
{
    /**
     * ScannerSubCategory constructor.
     */
    public function __construct()
    {
        $this->setCategoryType(self::TYPE_IS_SCAN);
    }
}
