<?php

namespace Dvsa\Olcs\Transfer\Command\Submission;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType as FieldType;

/**
 * @Transfer\RouteName("backend/submission/single/store")
 * @Transfer\Method("POST")
 */
final class StoreSubmissionSnapshot extends AbstractCommand
{
    use FieldType\Traits\Identity;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Escape(false)
     */
    protected $html;

    /**
     * Get the HTML content
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }
}
