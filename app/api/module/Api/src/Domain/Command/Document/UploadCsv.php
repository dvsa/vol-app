<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Category;
use Dvsa\Olcs\Transfer\FieldType\Traits\SubCategory;
use Dvsa\Olcs\Transfer\FieldType\Traits\UserOptional;

final class UploadCsv extends AbstractCommand
{
    use Category;
    use SubCategory;
    use UserOptional;

    /**
     * @var array
     */
    protected $csvContent;

    /**
     * @var string
     */
    protected $fileDescription;

    /**
     * Gets the value of csvContent
     *
     * @return array
     */
    public function getCsvContent(): array
    {
        return $this->csvContent;
    }

    /**
     * Gets the value of fileDescription
     *
     * @return string
     */
    public function getFileDescription(): string
    {
        return $this->fileDescription;
    }
}
