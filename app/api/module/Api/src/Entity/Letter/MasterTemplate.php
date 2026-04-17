<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * MasterTemplate Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="master_template")
 */
class MasterTemplate extends AbstractMasterTemplate
{
    public const LOCALE_EN_GB = 'en_GB';
    public const LOCALE_CY_GB = 'cy_GB';

    /**
     * Check if this is the default template for a locale
     *
     * @return bool
     */
    public function isDefaultForLocale()
    {
        return $this->isDefault;
    }

    /**
     * Set this template as the default for its locale
     *
     * @return self
     */
    public function makeDefault()
    {
        $this->isDefault = true;
        return $this;
    }

    /**
     * Remove default status
     *
     * @return self
     */
    public function removeDefault()
    {
        $this->isDefault = false;
        return $this;
    }
}
