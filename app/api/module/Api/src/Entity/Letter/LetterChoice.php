<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterChoice Entity
 */
#[ORM\Table(name: 'letter_choice')]
#[ORM\Entity]
class LetterChoice extends AbstractLetterChoice
{
    /**
     * The keys of the "First or final request" choices. A letter with one of these is chased up
     * with a task once it's sent (VOL-7714).
     *
     * A choice's key can be edited in the letter admin screens. If one of these two is changed
     * there, its letters stop getting the task until this is changed to match.
     */
    public const KEY_FIRST_REQUEST = 'first';
    public const KEY_FINAL_REQUEST = 'final';

    /**
     * Whether this choice belongs on a letter with the given Goods/PSV. Null on either side means any
     *
     * @param string|null $goodsOrPsv lcat_gv, lcat_psv or null when the letter has no licence
     * @return bool
     */
    public function appliesToGoodsOrPsv(?string $goodsOrPsv): bool
    {
        return $goodsOrPsv === null
            || $this->goodsOrPsv === null
            || $this->goodsOrPsv->getId() === $goodsOrPsv;
    }
}
