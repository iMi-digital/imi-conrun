<?php

namespace IMI\Contao\Command\Database\Compressor;

use IMI\Util\OperatingSystem;

abstract class AbstractCompressor
{
    /**
     * Returns the command line for compressing the dump file.
     *
     * @param string $command
     * @param bool $pipe
     * @return string
     */
    abstract public function getCompressingCommand($command, $pipe = true);

    /**
     * Returns the command line for decompressing the dump file.
     *
     * @param string $command MySQL client tool connection string
     * @param string $fileName Filename (shell argument escaped)
     * @param bool $pipe
     * @return string
     */
    abstract public function getDecompressingCommand($command, $fileName, $pipe = true);

    /**
     * Returns the file name for the compressed dump file.
     *
     * @param string $fileName
     * @param bool $pipe
     * @return string
     */
    abstract public function getFileName($fileName, $pipe = true);

    /**
     * Check whether pv is installed
     *
     * @return bool
     */
    protected function hasPipeViewer()
    {
        return OperatingSystem::isProgramInstalled('pv');
    }
}