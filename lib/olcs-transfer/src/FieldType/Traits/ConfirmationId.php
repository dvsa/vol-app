<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
trait ConfirmationId
{
    /**
     * @var String
     */
    #[Transfer\Validator('Laminas\Validator\StringLength', options: ['min' => 1, 'max' => 255])]
    protected ?string $confirmationId = null;

    /**
     * @return ?string
     */
    public function getConfirmationId(): ?string
    {
        return $this->confirmationId;
    }
}
