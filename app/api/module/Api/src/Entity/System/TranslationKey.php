<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\DeletableInterface;

/**
 * TranslationKey Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="translation_key",
 *    indexes={
 *        @ORM\Index(name="fk_translation_key_users_created_by", columns={"created_by"}),
 *        @ORM\Index(name="fk_translation_key_users_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class TranslationKey extends AbstractTranslationKey implements DeletableInterface
{
    /**
     * Checks whether there are translated texts still attached to the key
     *
     * @return boolean
     */
    public function canDelete()
    {
        return $this->translationKeyTexts->isEmpty();
    }

    /**
     * Creates a translation key record
     *
     * @return TranslationKey
     */
    public static function create(string $translationKey, string $description)
    {
        $instance = new self();

        $instance->translationKey = $translationKey;
        $instance->description = $description;

        return $instance;
    }
}
