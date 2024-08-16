<?php

namespace Dvsa\Olcs\Api\Entity\Template;

use Doctrine\ORM\Mapping as ORM;

/**
 * TemplateTestData Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="template_test_data",
 *    indexes={
 *        @ORM\Index(name="ix_template_test_data_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_template_test_data_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class TemplateTestData extends AbstractTemplateTestData
{
    /**
     * Get the test data as a PHP array
     *
     * @return array
     */
    public function getDecodedJson()
    {
        return json_decode($this->json, true);
    }
}
