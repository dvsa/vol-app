<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * EditorJS Comment Field
 */
trait EditorJsComment
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\IsJsonString")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":5})
     * @Transfer\Escape(false)
     */
    protected $comment;

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
