<?php

namespace Dvsa\Olcs\Email\Transport;

use Aws\S3\S3Client;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mailer\Envelope;

final class ArchivingMailer implements MailerInterface
{
    public function __construct(
        private MailerInterface $inner,
        private S3Client $s3,
        private string $bucket
    ) {}

    public function send(RawMessage $message, ?Envelope $envelope = null): void
    {
        $this->inner->send($message, $envelope);

        $raw = $message->toString();
        $key = sprintf('emails/%s-%s.eml', gmdate('Y/m/d/His'), bin2hex(random_bytes(4)));

        $this->s3->putObject([
            'Bucket'      => $this->bucket,
            'Key'         => $key,
            'Body'        => $raw,
            'ContentType' => 'message/rfc822',
        ]);
    }
}
