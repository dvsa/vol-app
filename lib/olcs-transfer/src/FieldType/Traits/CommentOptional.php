<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Optional Comment
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait CommentOptional
{
    /**
     * @var String
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":4000})
     */
    protected $comment;

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
}
