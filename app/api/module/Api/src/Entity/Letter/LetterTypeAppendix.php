<?php

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterTypeAppendix Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_type_appendix",
 *    indexes={
 *        @ORM\Index(name="ix_letter_type_appendix_letter_appendix_version_id", columns={"letter_appendix_version_id"}),
 *        @ORM\Index(name="ix_letter_type_appendix_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_type_appendix_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class LetterTypeAppendix extends AbstractLetterTypeAppendix
{
    /**
     * Check if appendix is mandatory
     *
     * @return bool
     */
    public function isMandatory()
    {
        return $this->isMandatory;
    }

    /**
     * Make appendix mandatory
     *
     * @return self
     */
    public function makeMandatory()
    {
        $this->isMandatory = true;
        return $this;
    }

    /**
     * Make appendix optional
     *
     * @return self
     */
    public function makeOptional()
    {
        $this->isMandatory = false;
        return $this;
    }

    /**
     * Get appendix name
     *
     * @return string
     */
    public function getAppendixName()
    {
        return $this->letterAppendixVersion->getName();
    }

    /**
     * Get appendix document
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document|null
     */
    public function getAppendixDocument()
    {
        return $this->letterAppendixVersion->getDocument();
    }
}