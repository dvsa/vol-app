<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
trait UploadedEvidence
{
    protected $uploadedEvidence;

    public function getUploadedEvidence()
    {
        return $this->uploadedEvidence;
    }
}
