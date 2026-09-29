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
