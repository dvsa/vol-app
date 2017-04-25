<?php

namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractUploadEvidenceController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Upload Evidence Controller
 */
class UploadEvidenceController extends AbstractUploadEvidenceController
{
    use ApplicationControllerTrait;

    protected $lva = 'variation';
}
