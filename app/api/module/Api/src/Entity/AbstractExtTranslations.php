<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * AbstractExtTranslations Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="ext_translations",
 *    indexes={
 *        @ORM\Index(name="ix_ext_translations_locale_object_class_foreign_key", columns={"locale", "object_class", "foreign_key"}),
 *        @ORM\Index(name="uk_ext_translations_locale_object_class_field_foreign_key", columns={"locale", "object_class", "field", "foreign_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_ext_translations_locale_object_class_field_foreign_key", columns={"locale", "object_class", "field", "foreign_key"})
 *    }
 * )
 */
abstract class AbstractExtTranslations implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;

    /**
     * Primary key.  Auto incremented if numeric.
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Locale
     *
     * @var string
     *
     * @ORM\Column(type="string", name="locale", length=8, nullable=false)
     */
    protected $locale = '';

    /**
     * Object class
     *
     * @var string
     *
     * @ORM\Column(type="string", name="object_class", length=255, nullable=false)
     */
    protected $objectClass = '';

    /**
     * Field
     *
     * @var string
     *
     * @ORM\Column(type="string", name="field", length=32, nullable=false)
     */
    protected $field = '';

    /**
     * Foreign key
     *
     * @var string
     *
     * @ORM\Column(type="string", name="foreign_key", length=64, nullable=false)
     */
    protected $foreignKey = '';

    /**
     * Content
     *
     * @var string
     *
     * @ORM\Column(type="text", name="content", nullable=true)
     */
    protected $content;


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return ExtTranslations
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the locale
     *
     * @param string $locale new value being set
     *
     * @return ExtTranslations
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the locale
     *
     * @return string     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the object class
     *
     * @param string $objectClass new value being set
     *
     * @return ExtTranslations
     */
    public function setObjectClass($objectClass)
    {
        $this->objectClass = $objectClass;

        return $this;
    }

    /**
     * Get the object class
     *
     * @return string     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }

    /**
     * Set the field
     *
     * @param string $field new value being set
     *
     * @return ExtTranslations
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get the field
     *
     * @return string     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set the foreign key
     *
     * @param string $foreignKey new value being set
     *
     * @return ExtTranslations
     */
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * Get the foreign key
     *
     * @return string     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Set the content
     *
     * @param string $content new value being set
     *
     * @return ExtTranslations
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the content
     *
     * @return string     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}