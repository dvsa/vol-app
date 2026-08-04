<?php

namespace Common\Controller\Traits;

use Common\Exception\File\InvalidMimeException;
use Common\Service\Helper\FileUploadHelperService;
use Common\Util\FileContent;
use Dvsa\Olcs\Transfer\Command\Document\DeleteDocument;
use Dvsa\Olcs\Transfer\Command\Document\Upload;

trait GenericUpload
{
    protected FileUploadHelperService $uploadHelper;

    /**
     * Process files
     *
     * @param \Laminas\Form\Form $form           Form
     * @param string          $selector       selector identifying the MultipleFileUpload element
     * @param callable        $uploadCallback method name for upload
     * @param callable        $deleteCallback method name for delete
     * @param callable        $loadCallback   method name for load
     * @param string          $countSelector  optional selector identifying element to
     *                                        update with number of files uploaded (e.g. for validation)
     *
     * @return bool
     */
    public function processFiles(
        $form,
        $selector,
        $uploadCallback,
        $deleteCallback,
        $loadCallback,
        $countSelector = null
    ) {
        $this->uploadHelper->setForm($form)
            ->setSelector($selector)
            ->setUploadCallback($uploadCallback)
            ->setDeleteCallback($deleteCallback)
            ->setLoadCallback($loadCallback)
            ->setRequest($this->getRequest());

        if (!is_null($countSelector)) {
            $this->uploadHelper->setCountSelector($countSelector);
        }

        return $this->uploadHelper->process();
    }

    /**
     * Upload a file
     *
     * @param array $fileData File Data
     * @param array $data     Data
     *
     * @return bool Always true unless Exception thrown
     * @throws InvalidMimeException
     * @throws \Exception
     */
    protected function uploadFile($fileData, $data)
    {
        if (!isset($data['filename'])) {
            if (isset($fileData['name'])) {
                $data['filename'] = $fileData['name'];
            } elseif (isset($fileData['filename'])) {
                $data['filename'] = $fileData['filename'];
            }
        }

        $mimeType = ($fileData['type'] ?? null);
        $data['content'] = new FileContent($fileData['tmp_name'], $mimeType);

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->handleCommand(Upload::create($data));

        if ($response->isClientError()) {
            $messages = $response->getResult()['messages'];

            if (isset($messages['ERR_EBSR_MIME'])) {
                throw new InvalidMimeException($messages['ERR_EBSR_MIME']);
            }

            if (isset($messages['ERR_MIME'])) {
                throw new InvalidMimeException();
            }
        }

        if ($response->isOk()) {
            return true;
        }

        throw new \Exception();
    }

    /**
     * Delete file
     *
     * @param int $id Identifier
     *
     * @return bool
     */
    public function deleteFile($id)
    {
        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->handleCommand(DeleteDocument::create(['id' => $id, 'unlinkLicence' => true]));

        return $response->isOk();
    }
}
