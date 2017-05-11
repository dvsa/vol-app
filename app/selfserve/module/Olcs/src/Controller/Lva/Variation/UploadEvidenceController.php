<?php

namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractUploadEvidenceController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * External Upload Evidence Controller
 */
class UploadEvidenceController extends AbstractUploadEvidenceController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
}
