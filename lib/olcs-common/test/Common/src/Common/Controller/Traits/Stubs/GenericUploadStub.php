<?php

declare(strict_types=1);

namespace CommonTest\Common\Controller\Traits\Stubs;

use Common\Controller\Traits\GenericUpload;
use Common\Service\Helper\FileUploadHelperService;
use Dvsa\Olcs\Transfer\Command\Document\DeleteDocument;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Laminas\Mvc\Controller\AbstractActionController;

class GenericUploadStub extends AbstractActionController
{
    use GenericUpload;

    public $stubResponse;
    protected FileUploadHelperService $uploadHelper;

    public function callUploadFile(array $fileData, array $data): bool
    {
        return $this->uploadFile($fileData, $data);
    }

    public function callDeleteFile(int $id): bool
    {
        return $this->deleteFile($id);
    }

    public function handleCommand(Upload|DeleteDocument $dto)
    {
        $this->stubResponse->dto = $dto;

        return $this->stubResponse;
    }
}
