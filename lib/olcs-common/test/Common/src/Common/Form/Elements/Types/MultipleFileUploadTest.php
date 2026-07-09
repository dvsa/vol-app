<?php

/**
 * MultipleFileUploadTest
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Form\Elements\Types;

use Common\Form\Elements\Types\MultipleFileUpload;

/**
 * MultipleFileUploadTest
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class MultipleFileUploadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the element configuration
     */
    public function testElement(): void
    {
        $element = new MultipleFileUpload();

        $this->assertSame('Upload file', $element->getLabel());

        $this->assertTrue($element->has('list'));
        $this->assertTrue($element->has('__messages__'));
        $this->assertTrue($element->has('file-controls'));
        $this->assertTrue($element->get('file-controls')->has('file'));
        $this->assertTrue($element->get('file-controls')->has('upload'));
    }
}
