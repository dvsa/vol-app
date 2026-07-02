<?php

namespace Dvsa\Olcs\Transfer\Query\Category;

use Dvsa\Olcs\Transfer\Query\PublicQueryCacheInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;

/**
 * @Transfer\RouteName("backend/category")
 */
class GetList extends AbstractQuery implements
    \Dvsa\Olcs\Transfer\Query\OrderedQueryInterface,
    CacheableLongTermQueryInterface,
    PublicQueryCacheInterface
{
    use \Dvsa\Olcs\Transfer\Query\OrderedTrait;

    /**
     * @var string
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     * @Transfer\Optional
     */
    protected $isTaskCategory;

    /**
     * @var string
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     * @Transfer\Optional
     */
    protected $isDocCategory;

    /**
     * @var string
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     * @Transfer\Optional
     */
    protected $isScanCategory;

    /**
     * @var string
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     * @Transfer\Optional
     */
    protected $isOnlyWithItems;

    /**
     * Get Is Task Category
     *
     * @return string
     */
    public function getIsTaskCategory()
    {
        return $this->isTaskCategory;
    }

    /**
     * Get is Doc Category
     *
     * @return string
     */
    public function getIsDocCategory()
    {
        return $this->isDocCategory;
    }

    /**
     * Get is Scan Category
     *
     * @return string
     */
    public function getIsScanCategory()
    {
        return $this->isScanCategory;
    }

    /**
     * Is Take only categories with Items
     *
     * @return string
     */
    public function getIsOnlyWithItems()
    {
        return $this->isOnlyWithItems;
    }
}
