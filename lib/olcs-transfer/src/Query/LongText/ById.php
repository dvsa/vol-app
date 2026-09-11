<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Query\LongText;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/long-text/update")
 */
final class ById extends AbstractQuery
{
    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    protected $id;

    public function getId(): ?int
    {
        return $this->id === null ? null : (int) $this->id;
    }
}
