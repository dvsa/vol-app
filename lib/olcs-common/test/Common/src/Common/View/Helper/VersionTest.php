<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\View\Helper\Version;
use Laminas\View\Renderer\RendererInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Test Version view helper
 */
final class VersionTest extends MockeryTestCase
{
    /**
     * Create a mock view that captures the data passed to template
     */
    private function createMockViewWithDataCapture(array &$capturedData): RendererInterface
    {
        $mockView = m::mock(RendererInterface::class);
        $mockView->shouldReceive('render')
            ->with(Version::TEMPLATE_PATH, m::capture($capturedData))
            ->andReturn('template rendered with captured data');
        return $mockView;
    }

    /**
     * Data provider for scenarios that should return empty string
     *
     * @return \Iterator<string, array<string, mixed>>
     */
    public static function emptyResultProvider(): \Iterator
    {
        yield 'missing version config' => [
            'config' => []
        ];
        yield 'invalid version config' => [
            'config' => ['version' => 'invalid']
        ];
        yield 'information bar disabled' => [
            'config' => [
                'version' => [
                    'show_information_bar' => false,
                    'environment' => 'test',
                    'description' => 'test',
                    'release' => '1.0'
                ]
            ]
        ];
        yield 'missing show_information_bar (defaults to false)' => [
            'config' => [
                'version' => [
                    'environment' => 'test',
                    'description' => 'test',
                    'release' => '1.0'
                ]
            ]
        ];
    }

    /**
     * Data provider for scenarios that should render markup
     *
     * @return \Iterator<string, array<string, mixed>>
     */
    public static function renderingProvider(): \Iterator
    {
        yield 'full version information' => [
            'config' => [
                'version' => [
                    'show_information_bar' => true,
                    'environment' => 'Unit Test',
                    'description' => 'DESCRIPTION',
                    'release' => '1.0'
                ]
            ],
            'expectedEnvironment' => 'Unit Test',
            'expectedDescription' => 'DESCRIPTION',
            'expectedRelease' => '1.0'
        ];
        yield 'missing values (null)' => [
            'config' => [
                'version' => [
                    'show_information_bar' => true,
                    'environment' => null,
                    'release' => null
                ]
            ],
            'expectedEnvironment' => Version::DEFAULT_UNDEFINED,
            'expectedDescription' => Version::DEFAULT_UNDEFINED,
            'expectedRelease' => Version::DEFAULT_UNDEFINED
        ];
        yield 'completely missing details' => [
            'config' => [
                'version' => [
                    'show_information_bar' => true
                ]
            ],
            'expectedEnvironment' => Version::DEFAULT_UNDEFINED,
            'expectedDescription' => Version::DEFAULT_UNDEFINED,
            'expectedRelease' => Version::DEFAULT_UNDEFINED
        ];
        yield 'empty string values' => [
            'config' => [
                'version' => [
                    'show_information_bar' => true,
                    'environment' => '',
                    'description' => '',
                    'release' => ''
                ]
            ],
            'expectedEnvironment' => Version::DEFAULT_EMPTY,
            'expectedDescription' => Version::DEFAULT_EMPTY,
            'expectedRelease' => Version::DEFAULT_EMPTY
        ];
        yield 'mixed missing and empty values' => [
            'config' => [
                'version' => [
                    'show_information_bar' => true,
                    'environment' => 'Production',
                    'description' => '',
                    // release is missing (will be null)
                ]
            ],
            'expectedEnvironment' => 'Production',
            'expectedDescription' => Version::DEFAULT_EMPTY,
            'expectedRelease' => Version::DEFAULT_UNDEFINED
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('emptyResultProvider')]
    public function testRenderReturnsEmptyString(array $config): void
    {
        $sut = new Version($config);
        $this->assertSame('', $sut->render());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderingProvider')]
    public function testRenderCallsPartialWithCorrectData(
        array $config,
        string $expectedEnvironment,
        string $expectedDescription,
        string $expectedRelease
    ): void {
        $capturedData = [];
        $mockView = $this->createMockViewWithDataCapture($capturedData);

        $sut = new Version($config);
        $sut->setView($mockView);

        $result = $sut->render();

        // Assert the template was called and we got the expected data
        $this->assertSame('template rendered with captured data', $result);
        $this->assertSame(
            $expectedEnvironment,
            $capturedData['environment'],
            "Environment value mismatch. Expected: '{$expectedEnvironment}', Actual: '{$capturedData['environment']}'"
        );
        $this->assertSame(
            phpversion(),
            $capturedData['phpVersion'],
            "PHP version value mismatch. Expected: '" . phpversion() . "', Actual: '{$capturedData['phpVersion']}'"
        );
        $this->assertSame(
            $expectedDescription,
            $capturedData['description'],
            "Description value mismatch. Expected: '{$expectedDescription}', Actual: '{$capturedData['description']}'"
        );
        $this->assertSame(
            $expectedRelease,
            $capturedData['release'],
            "Release value mismatch. Expected: '{$expectedRelease}', Actual: '{$capturedData['release']}'"
        );
    }

    /**
     * Test that __invoke() method delegates to render()
     */
    public function testInvokeMethodDelegatesToRender(): void
    {
        $config = [
            'version' => [
                'show_information_bar' => true,
                'environment' => 'Test Environment',
                'description' => 'Test Description',
                'release' => '2.0'
            ]
        ];
        $mockView = m::mock(RendererInterface::class);
        $mockView->shouldReceive('render')
            ->with(Version::TEMPLATE_PATH, m::any())
            ->twice()
            ->andReturn('test output');
        $sut = new Version($config);
        $sut->setView($mockView);

        $this->assertSame($sut->render(), $sut());
    }
}
