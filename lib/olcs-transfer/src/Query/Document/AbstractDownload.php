<?php

namespace Dvsa\Olcs\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\LoggerOmitResponseInterface;
use Dvsa\Olcs\Transfer\Query\StreamInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class AbstractDownload extends AbstractQuery implements
    LoggerOmitResponseInterface,
    StreamInterface
{
    /**
     * @var  bool
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Boolean")
     */
    protected $isInline;

    /**
     * Inline or download
     *
     * @return boolean
     */
    public function isInline()
    {
        return $this->isInline;
    }
}
