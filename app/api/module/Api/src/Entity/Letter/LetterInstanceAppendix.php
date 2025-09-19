<?php

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterInstanceAppendix Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_instance_appendix",
 *    indexes={
 *        @ORM\Index(name="ix_letter_instance_appendix_letter_appendix_version_id", columns={"letter_appendix_version_id"}),
 *        @ORM\Index(name="ix_letter_instance_appendix_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_instance_appendix_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class LetterInstanceAppendix extends AbstractLetterInstanceAppendix
{
}