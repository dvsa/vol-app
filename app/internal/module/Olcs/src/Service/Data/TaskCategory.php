<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractListDataServiceServices;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class TaskCategory extends Category
{
    /**
     * Create service instance
     *
     * @param AbstractListDataServiceServices $abstractListDataServiceServices
     *
     * @return TaskCategory
     */
    public function __construct(AbstractListDataServiceServices $abstractListDataServiceServices)
    {
        parent::__construct($abstractListDataServiceServices);

        $this->setCategoryType(self::TYPE_IS_TASK);
    }
}
