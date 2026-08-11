<?php

namespace Dvsa\Olcs\Api\Entity\Doc;

use Doctrine\ORM\Mapping as ORM;

/**
 * DocParagraph Entity
 */
#[ORM\Table(name: 'doc_paragraph')]
#[ORM\Index(name: 'ix_doc_paragraph_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_doc_paragraph_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Entity]
class DocParagraph extends AbstractDocParagraph
{
}
