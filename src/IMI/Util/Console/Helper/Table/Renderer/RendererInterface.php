<?php

namespace IMI\Util\Console\Helper\Table\Renderer;

use Symfony\Component\Console\Output\OutputInterface;

interface RendererInterface
{
    /**
     * @param OutputInterface $output
     * @param array $rows
     */
    public function render(OutputInterface $output, array $rows);
}