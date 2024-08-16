<?php

namespace Dvsa\Olcs\Api\Entity\Pi;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\DeletableInterface;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * PresidingTc Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="presiding_tc")
 */
class PresidingTc extends AbstractPresidingTc implements DeletableInterface
{
    /**
     * @return PresidingTc
     */
    public static function create(string $name, User $user)
    {
        $instance = new self();
        $instance->name = $name;
        $instance->user = $user;
        return $instance;
    }

    /**
     * @return $this
     */
    public function update(string $name, User $user)
    {
        $this->name = $name;
        $this->user = $user;
        return $this;
    }

    /**
     * Can presiding tc be deleted?
     *
     * @return boolean
     */
    public function canDelete()
    {
        return true;
    }
}
