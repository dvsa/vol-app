<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;

/**
 * Language Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="language",
 *    indexes={
 *        @ORM\Index(name="fk_language_users_created_by", columns={"created_by"}),
 *        @ORM\Index(name="fk_language_users_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class Language extends AbstractLanguage
{
    public const SUPPORTED_LANGUAGES = [
        'en_GB' => [
            'id' => 1,
            'label' => 'English'
        ],
        'cy_GB' => [
            'id' => 2,
            'label' => 'Welsh'
        ],
        'en_NI' => [
            'id' => 3,
            'label' => 'English (NI)'
        ],
        'cy_NI' => [
            'id' => 4,
            'label' => 'Welsh (NI)'
        ],
    ];
}
