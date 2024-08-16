<?php

namespace Dvsa\Olcs\Api\Entity\Doc;

use Doctrine\ORM\Mapping as ORM;

/**
 * DocBookmark Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="doc_bookmark",
 *    indexes={
 *        @ORM\Index(name="ix_doc_bookmark_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_doc_bookmark_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class DocBookmark extends AbstractDocBookmark
{
}
