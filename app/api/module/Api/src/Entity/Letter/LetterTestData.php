<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterTestData Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_test_data")
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
