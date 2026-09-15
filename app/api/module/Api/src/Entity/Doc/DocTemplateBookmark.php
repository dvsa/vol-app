<?php

namespace Dvsa\Olcs\Api\Entity\Doc;

use Doctrine\ORM\Mapping as ORM;

/**
 * DocTemplateBookmark Entity
 */
#[ORM\Table(name: 'doc_template_bookmark')]
#[ORM\Index(name: 'ix_doc_template_bookmark_doc_bookmark_id', columns: ['doc_bookmark_id'])]
#[ORM\Index(name: 'ix_doc_template_bookmark_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_doc_template_bookmark_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Index(name: 'IDX_851FEE735653D501', columns: ['doc_template_id'])]
#[ORM\UniqueConstraint(name: 'uk_doc_template_bookmark_doc_template_id_doc_bookmark_id', columns: ['doc_template_id', 'doc_bookmark_id'])]
#[ORM\Entity]
class DocTemplateBookmark extends AbstractDocTemplateBookmark
{
}
