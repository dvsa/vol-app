<?php

/**
 * Bulk send email
 *
 * @author Andrew Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Api\Domain\Command\BulkSend;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

class Email extends AbstractCommand
{
    use User;

    /**
     * @var String
     */
    #[Transfer\Filter("Laminas\Filter\StringTrim")]
    protected $templateName;

    /**
     * @var String
     */
    #[Transfer\Filter("Laminas\Filter\StringTrim")]
    protected $documentIdentifier;

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * @return string
     */
    public function getDocumentIdentifier()
    {
        return $this->documentIdentifier;
    }
}
