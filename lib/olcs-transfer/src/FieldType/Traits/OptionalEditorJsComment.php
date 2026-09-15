<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Optional EditorJS Comment Field
 */
trait OptionalEditorJsComment
{
    /**
     * @Transfer\Filter("Laminas\Filter\ToNull")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\IsJsonString")
     * @Transfer\Optional
     * @Transfer\Escape(false)
     */
    protected $comment;

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
