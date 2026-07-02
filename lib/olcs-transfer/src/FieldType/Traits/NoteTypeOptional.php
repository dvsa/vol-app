<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait NoteType
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait NoteTypeOptional
{
    /**
     * @var String
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": {
     *              "note_t_app",
     *              "note_t_bus",
     *              "note_t_case",
     *              "note_t_lic",
     *              "note_t_org",
     *              "note_t_permit",
     *              "note_t_person",
     *              "note_t_tm"
     *          }
     *      }
     * )
     */
    protected $noteType;

    /**
     * @return string
     */
    public function getNoteType()
    {
        return $this->noteType;
    }
}
