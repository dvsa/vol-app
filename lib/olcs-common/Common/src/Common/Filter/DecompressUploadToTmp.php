<?php

namespace Common\Filter;

use Laminas\Filter\AbstractFilter;
use Laminas\Filter\Exception;

class DecompressUploadToTmp extends DecompressToTmp
{
    /**
     * @throws Exception\RuntimeException If filtering $value is impossible
     */
    #[\Override]
    public function filter(mixed $value): mixed
    {
        $tmpDir = $this->createTmpDir();

        $adapterOptions = $this->getDecompressFilter()->getAdapterOptions();
        $adapterOptions['target'] = $tmpDir;
        $this->getDecompressFilter()->setAdapterOptions($adapterOptions);

        $value['extracted_dir'] = $tmpDir;
        $this->getDecompressFilter()->filter($value['tmp_name']);

        return $value;
    }
}
