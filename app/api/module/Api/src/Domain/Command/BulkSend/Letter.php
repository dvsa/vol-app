<?php

/**
 * Bulk send Letters
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Api\Domain\Command\BulkSend;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;

class Letter extends AbstractCommand
{
    use User;

    /**
     * @var String
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $templateSlug;

    /**
     * @var String
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $documentIdentifier;

    /**
     * @return string
     */
    public function getTemplateSlug()
    {
        return $this->templateSlug;
    }

    /**
     * @return string
     */
    public function getDocumentIdentifier()
    {
        return $this->documentIdentifier;
    }
}
