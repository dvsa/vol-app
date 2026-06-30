<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Transport;

use Dvsa\Olcs\Email\Exception\EmailNotSentException;
use Dvsa\Olcs\Email\Transport\NotifyAttachmentValidator;
use PHPUnit\Framework\TestCase;

class NotifyAttachmentValidatorTest extends TestCase
{
    public function testAcceptsAllowedExtension(): void
    {
        NotifyAttachmentValidator::assertAllowed('document.pdf', 'pdf content');
        $this->addToAssertionCount(1);
    }

    public function testRejectsDisallowedExtension(): void
    {
        $this->expectException(EmailNotSentException::class);
        $this->expectExceptionMessage('disallowed extension "exe"');
        NotifyAttachmentValidator::assertAllowed('malware.exe', 'x');
    }

    public function testRejectsOversizedAttachment(): void
    {
        $content = str_repeat('x', NotifyAttachmentValidator::MAX_BYTES + 1);
        $this->expectException(EmailNotSentException::class);
        $this->expectExceptionMessage('exceeds the');
        NotifyAttachmentValidator::assertAllowed('big.pdf', $content);
    }

    public function testRejectsMissingExtension(): void
    {
        $this->expectException(EmailNotSentException::class);
        NotifyAttachmentValidator::assertAllowed('noext', 'x');
    }
}
