<?php

namespace Dvsa\Olcs\Email\Transport;

use Aws\S3\Exception\S3Exception;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Laminas\I18n\Filter\Alnum;
use Laminas\Log\Logger;
use Laminas\Mail\Transport\Exception\RuntimeException;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\File;
use Laminas\Mail\Transport\TransportInterface;

/**
 * Class S3File
 */
class S3File implements TransportInterface
{
    /**
     * @var S3FileOptions
     */
    protected $options;

    /**
     * @var File
     */
    private $fileTransport;

    /**
     * S3File constructor.
     *
     * @param File|null $fileTransport File Transport to use for creating the file
     */
    public function __construct(File $fileTransport = null)
    {
        if ($fileTransport === null) {
            $fileTransport = new File();
        }
        $this->fileTransport = $fileTransport;
    }

    /**
     * Sets options
     *
     * @param S3FileOptions $options Options
     *
     * @return void
     */
    public function setOptions(S3FileOptions $options)
    {
        $this->options = $options;
    }

    /**
     * Get options
     *
     * @return S3FileOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Saves e-mail message to a file and upload it to S3
     *
     * @param Message $message Email message
     *
     * @return void
     */
    public function send(Message $message)
    {
        $this->fileTransport->send($message);
        $file = $this->fileTransport->getLastFile();
        $filter = new Alnum(true);
        $s3FileName = substr(str_replace(' ', '_', $filter->filter($message->getSubject())), 0, 100);
        $s3Client = $this->getOptions()->getS3Client();
        $bucket = $this->getOptions()->getS3Bucket();
        $key = $this->getOptions()->getS3Key();

        try {
            $s3Client->putObject([
                'Bucket' => $bucket,
                'Key' => $key . "/" . $s3FileName,
                'Body' => file_get_contents($file)
            ]);
        } catch (S3Exception $e) {
            throw new RuntimeException('Cannot send mail to S3 : ' . $e->getAwsErrorMessage());
        } finally {
            //clean up email file
            $this->deleteFile($file);
        }
    }

    /**
     * Delete a file
     *
     * @param string $file File to delete
     *
     * @return void
     */
    protected function deleteFile($file)
    {
        unlink($file);
    }
}
