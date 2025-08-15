<?php

namespace Dvsa\Olcs\Api\Entity\Si;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Doc\Document;

/**
 * ErruRequestFailure Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="erru_request_failure",
 *    indexes={
 *        @ORM\Index(name="ix_erru_request_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_erru_request_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_erru_request_failure_document_id", columns={"document_id"})
 *    }
 * )
 */
class ErruRequestFailure extends AbstractErruRequestFailure
{
    /**
     * Creates erru request failure record
     *
     * @param Document     $document the document
     * @param array        $errors   array of errors
     * @param array|string $input    usually array, if it's a string we don't save it
     */
    public function __construct(Document $document, array $errors, $input)
    {

        $this->document = $document;
        $this->errors = json_encode($errors);

        if (is_array($input)) {
            $this->input = json_encode($input);
        }
    }
}
