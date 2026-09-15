<?php

namespace Common\Filter;

use Laminas\Filter\AbstractFilter;
use Laminas\Filter\Decompress;
use Common\Filesystem\Filesystem;

/**
 * @template-extends AbstractFilter<mixed, mixed>
 */
class DecompressToTmp extends AbstractFilter
{
    protected Decompress $decompressFilter;
    protected string $tempRootDir;
    protected Filesystem $fileSystem;

    public function setDecompressFilter(Decompress $decompressFilter): static
    {
        $this->decompressFilter = $decompressFilter;
        return $this;
    }

    public function getDecompressFilter(): Decompress
    {
        return $this->decompressFilter;
    }

    public function setTempRootDir($tempRootDir): static
    {
        $this->tempRootDir = $tempRootDir;
        return $this;
    }

    public function getTempRootDir(): string
    {
        return $this->tempRootDir;
    }

    public function setFileSystem($fileSystem): static
    {
        $this->fileSystem = $fileSystem;
        return $this;
    }

    public function getFileSystem()
    {
        return $this->fileSystem;
    }

    /**
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection -
     */
    #[\Override]
    public function filter(mixed $value): mixed
    {
        $tmpDir = $this->createTmpDir();

        $adapterOptions = $this->getDecompressFilter()->getAdapterOptions();
        $adapterOptions['target'] = $tmpDir;
        $this->getDecompressFilter()->setAdapterOptions($adapterOptions);

        return $this->getDecompressFilter()->filter($value);
    }

    protected function createTmpDir(): string
    {
        $filesystem = $this->getFileSystem();
        $tmpDir = $filesystem->createTmpDir($this->getTempRootDir(), 'zip');

        register_shutdown_function(
            static function () use ($tmpDir, $filesystem) {
                $filesystem->remove($tmpDir);
            }
        );

        return $tmpDir;
    }
}
