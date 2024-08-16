<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;

/**
 * AbstractBrRegOrVary Bookmark
 */
abstract class AbstractBrRegOrVary extends DynamicBookmark
{
    protected $renderReg;
    protected $renderVary;

    /**
     * Get the query to retrieve date required by the bookmark
     *
     * @param array $data Data
     *
     * @return Qry
     */
    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data[DynamicBookmark::PARAM_BUSREG_ID]]);
    }

    /**
     * Render the bookmark
     *
     * @return string
     * @throws \Exception
     */
    public function render()
    {
        if (isset($this->data['variationNo'])) {
            return $this->data['variationNo'] > 0 ? $this->renderVary : $this->renderReg;
        }

        throw new \Exception('Failed to generate bookmark ' . static::class);
    }
}
