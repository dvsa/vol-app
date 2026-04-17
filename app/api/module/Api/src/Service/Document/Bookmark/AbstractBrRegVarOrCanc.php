<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;

/**
 * AbstractBrRegVarOrCanc Bookmark
 */
abstract class AbstractBrRegVarOrCanc extends SingleValueAbstract
{
    public const SRCH_FLD_KEY = 'id';
    public const SRCH_VAL_KEY = DynamicBookmark::PARAM_BUSREG_ID;
    public const BUNDLE = ['status'];
    public const QUERY_CLASS = Qry::class;

    protected $renderNew;
    protected $renderVar;
    protected $renderCanc;

    /**
     * Render the bookmark
     *
     * @return string
     * @throws \Exception
     */
    #[\Override]
    public function render()
    {
        if (!empty($this->data['status']['id'])) {
            switch ($this->data['status']['id']) {
                case BusReg::STATUS_NEW:
                    return $this->renderNew;
                case BusReg::STATUS_VAR:
                    return $this->renderVar;
                case BusReg::STATUS_CANCEL:
                    return $this->renderCanc;
            }
        }

        throw new \Exception('Failed to generate bookmark ' . static::class);
    }
}
