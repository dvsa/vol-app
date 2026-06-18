<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

trait TokenId
{
    /**
     * @var String
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":9999})
     */
    protected ?string $tokenId = null;

    /**
     * @return ?string
     */
    public function getTokenId(): ?string
    {
        return $this->tokenId;
    }
}
