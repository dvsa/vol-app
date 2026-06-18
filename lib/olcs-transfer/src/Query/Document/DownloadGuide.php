<?php

namespace Dvsa\Olcs\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\FieldType\Traits\IsSlugOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/document/download-guide")
 */
class DownloadGuide extends AbstractDownload
{
    use IsSlugOptional;

    /**
     * @var  string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $identifier;

    /**
     * Get file identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
