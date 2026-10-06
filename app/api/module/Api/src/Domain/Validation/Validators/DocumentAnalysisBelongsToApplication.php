<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Document Analysis Belongs To Application
 *
 * Confirms a document analysis was run for a given application (new or variation).
 */
class DocumentAnalysisBelongsToApplication extends AbstractBelongsToApplication
{
    protected $repo = 'DocumentAnalysis';
}

