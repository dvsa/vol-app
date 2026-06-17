<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

trait UploadedEvidence
{
    protected $uploadedEvidence;

    public function getUploadedEvidence()
    {
        return $this->uploadedEvidence;
    }
}
