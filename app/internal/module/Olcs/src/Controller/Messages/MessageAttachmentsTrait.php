<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Common\Category;
use Common\Controller\Traits\GenericUpload;
use Dvsa\Olcs\Transfer\Query\Messaging\Documents;
use Laminas\Form\Form;

trait MessageAttachmentsTrait
{
    use GenericUpload;

    protected function handleAttachmentPost(Form $form, string $selector): bool
    {
        if ($this->getPostedCorrelationId() === null) {
            return false;
        }

        $this->processFiles(
            $form,
            $selector,
            $this->processFileUpload(...),
            $this->deleteFile(...),
            $this->getUploadedFiles(...),
        );

        return $this->isUploadOrRemovePost($selector);
    }

    public function processFileUpload(array $file): void
    {
        $this->uploadFile($file, [
            'category'      => Category::CATEGORY_LICENSING,
            'subCategory'   => Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS,
            'description'   => $file['name'],
            'isExternal'    => false,
            'correlationId' => $this->getPostedCorrelationId(),
        ]);
    }

    public function getUploadedFiles(): array
    {
        $response = $this->handleQuery(Documents::create(['correlationId' => $this->getPostedCorrelationId()]));

        return $response->isOk() ? $response->getResult() : [];
    }

    protected function getPostedCorrelationId(): ?string
    {
        $posted = $this->params()->fromPost('correlationId');

        return is_string($posted) && $posted !== '' ? $posted : null;
    }

    protected function newCorrelationId(): string
    {
        return bin2hex(random_bytes(20));
    }

    private function isUploadOrRemovePost(string $selector): bool
    {
        $posted = (array) $this->params()->fromPost();
        foreach (explode('->', $selector) as $name) {
            $posted = $posted[$name] ?? [];
        }

        if (isset($posted['upload'])) {
            return true;
        }

        foreach ((array) ($posted['list'] ?? []) as $file) {
            if (!empty($file['remove'])) {
                return true;
            }
        }

        return false;
    }
}
