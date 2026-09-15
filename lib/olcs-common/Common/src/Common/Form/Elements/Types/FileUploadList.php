<?php

namespace Common\Form\Elements\Types;

use Laminas\Form\Fieldset;
use Common\Form\Elements\Types\FileUploadListItem;
use Common\Form\Elements\Types\Html;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Submit;

/**
 * File Upload List
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FileUploadList extends Fieldset
{
    /**
     * @var array array of image extensions that can be previewed
     */
    protected $previewableExtensions = ['gif', 'jpg', 'jpeg', 'bmp', 'png'];

    /**
     * @var array array of image extensions that can not be previewed
     */
    protected $otherImagesExtensions = ['tif', 'tiff'];

    /**
     * Set the files in the file list
     *
     * @param array  $fileData Array of file data
     * @param object $url      UrlHelperService
     */
    public function setFiles($fileData = [], $url = null): void
    {
        /** (VOL-2693) this code is used everywhere but we don't seem to be provided an array in all cases */
        if (!is_array($fileData)) {
            return;
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        foreach ($fileData as $file) {
            $file['url'] = $url->fromRoute(
                'getfile',
                ['identifier' => $file['id']]
            );

            $size = $file['size'];
            $unit = 0;

            while ($size > 1024) {
                $size /= 1024;
                ++$unit;
            }

            $file['size'] = round($size, 1) . $units[$unit];

            $fileItem = new FileUploadListItem('file-' . $file['id']);
            $fileItem->setAttribute('class', 'file');

            $id = new Hidden('id');
            $id->setValue($file['id']);

            $version = new Hidden('version');
            $version->setValue($file['version']);

            $html = new Html('link', ['render-container' => false]);
            $html->setAttribute('data-container-class', 'file-upload');
            $html->setValue(
                '<a class="govuk-link" href="' . $file['url'] . '">'
                . $file['description'] . '</a> <span>' . $file['size'] . '</span>'
            );

            $remove = new Submit('remove', ['render-container' => false]);
            $remove->setValue('Remove');
            $remove->setAttribute('class', 'file__remove action-button-link');
            $remove->setAttribute('data-container-class', 'file-upload');

            $fileItem->add($html);
            $fileItem->add($remove);
            $fileItem->add($id);
            $fileItem->add($version);

            if ($this->getOption('preview_images') === true) {
                // show image previews if permitted
                if ($this->isPreviewableImage($file)) {
                    $imagePreview = new Html('preview', ['render-container' => false]);
                    $imagePreview->setValue(
                        '<div class="file__image-container"><img src="' . $file['url'] . '" /></div>'
                    );
                    $fileItem->add($imagePreview);
                    // show "now available" message for some extensions
                } elseif ($this->hasOtherExtension($file)) {
                    $noPreview = new Html('preview');
                    $noPreview->setValue('Preview is not available');
                    $fileItem->add($noPreview);
                }
            }

            $this->add($fileItem);
        }
    }

    /**
     * Is this file an image we can preview?
     *
     * @param array $file File data
     *
     * @return bool
     */
    private function isPreviewableImage($file)
    {
        return in_array(
            strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION)),
            $this->getPreviewableExtensions()
        );
    }

    /**
     * Return list of image extensions we can preview
     *
     * @return mixed
     */
    public function getPreviewableExtensions()
    {
        return $this->previewableExtensions;
    }

    /**
     * Return list of other image extensions we can't preview
     *
     * @return mixed
     */
    public function getOtherImagesExtensions()
    {
        return $this->otherImagesExtensions;
    }

    /**
     * Has other extension
     *
     * @param array $file file
     *
     * @return bool
     */
    private function hasOtherExtension($file)
    {
        return (
            in_array(
                strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION)),
                $this->getOtherImagesExtensions()
            )
        );
    }
}
