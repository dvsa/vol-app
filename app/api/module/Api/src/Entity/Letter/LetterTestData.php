<?php

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterTestData Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_test_data",
 *    indexes={
 *        @ORM\Index(name="ix_letter_test_data_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_test_data_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class LetterTestData extends AbstractLetterTestData
{
    /**
     * Get the decoded JSON data
     *
     * @return array
     */
    public function getDecodedJson()
    {
        return $this->json ?: [];
    }

    /**
     * Set JSON data from array
     *
     * @param array $data
     * @return self
     */
    public function setJsonFromArray(array $data)
    {
        $this->json = $data;
        return $this;
    }
}