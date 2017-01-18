<?php

namespace Olcs\Service\Data;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class DocumentCategoryWithDocs extends Category
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->setCategoryType(self::TYPE_IS_DOC);
        $this->setIsOnlyWithItems(true);
    }
}
