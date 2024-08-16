<?php

/**
 * Bulk send email
 *
 * @author Andrew Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Api\Domain\Command\BulkSend;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

class ProcessEmail extends AbstractCommand
{
    use Identity;

    /**
     * @var String
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $templateName;

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }
}
