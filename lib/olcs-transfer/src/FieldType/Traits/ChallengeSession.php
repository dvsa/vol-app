<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

trait ChallengeSession
{
    /**
     * @var String
     */
    #[Transfer\Validator('Laminas\Validator\StringLength', options: ['min' => 20, 'max' => 2048])]
    protected ?string $challengeSession = null;

    /**
     * @return ?string
     */
    public function getChallengeSession(): ?string
    {
        return $this->challengeSession;
    }
}
