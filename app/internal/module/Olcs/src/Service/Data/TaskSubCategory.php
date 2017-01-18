<?php

namespace Olcs\Service\Data;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class TaskSubCategory extends SubCategory
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->setCategoryType(self::TYPE_IS_TASK);
    }
}
