<?php

/**
 * Trailers Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\Trailers;

/**
 * Trailers Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TrailersTest extends \PHPUnit\Framework\TestCase
{
    public function testMapFromResult(): void
    {
        $data = [
            'organisation' => [
                'confirmShareTrailerInfo' => 'Y'
            ]
        ];

        $expected = [
            'trailers' => [
                'shareInfo' => 'Y'
            ]
        ];

        $this->assertEquals($expected, Trailers::mapFromResult($data));
    }
}
