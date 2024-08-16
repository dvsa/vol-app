<?php

namespace Dvsa\Olcs\Api\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserPasswordReset Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="user_password_reset",
 *    indexes={
 *        @ORM\Index(name="ix_user_password_reset_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_user_password_reset_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_user_password_reset_user_id", columns={"user_id"})
 *    }
 * )
 */
class UserPasswordReset extends AbstractUserPasswordReset
{
    /**
     *
     * @return self
     */
    public static function create(User $user, string $confirmation): self
    {
        $entity = new self();

        $validTo = new \DateTime();
        $validTo->modify('+12 hours');

        $entity->setUser($user);
        $entity->setConfirmation($confirmation);
        $entity->setSuccess(false);
        $entity->setValidTo($validTo);

        return $entity;
    }

    /**
     * @return bool
     */
    public function isValid(string $username): bool
    {
        //if already used successfully, it can't be used again
        if ($this->success) {
            return false;
        }

        $currentDate = new \DateTime();

        //if validity period has passed, a new reset will be needed
        if ($this->getValidTo(true) < $currentDate) {
            return false;
        }

        //make sure provided username matches
        if ($username !== $this->user->getLoginId()) {
            return false;
        }

        //unlikely to be the case, but recheck the user is still eligible for a reset
        return $this->user->canResetPassword();
    }
}
