<?php

namespace phparsenal\fastforward\Console;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Tone the Symfony defaults down a bit
 */
class ConsoleStyle extends SymfonyStyle
{
    /**
     * {@inheritdoc}
     */
    public function success($message)
    {
        $this->block($message, 'OK', 'fg=green', ' ');
    }

    /**
     * {@inheritdoc}
     */
    public function error($message)
    {
        $this->block($message, 'ERROR', 'fg=red', ' ');
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message)
    {
        $this->block($message, 'WARNING', 'fg=white;bg=red', ' ', false);
    }

    /**
     * {@inheritdoc}
     */
    public function note($message)
    {
        $this->block($message, 'NOTE', 'fg=yellow', ' ');
    }

    /**
     * {@inheritdoc}
     */
    public function caution($message)
    {
        $this->block($message, 'CAUTION', 'fg=red', ' ', true);
    }

    /**
     * {@inheritdoc}
     */
    public function table(array $headers, array $rows)
    {
        $table = new Table($this);
        $table->setTerminalWidth($this->getTerminalWidth());
        $table->setHeaders($headers);
        $table->addRows($rows);
        $table->render();
    }

    private function getTerminalWidth()
    {
        $application = new \Symfony\Component\Console\Application();
        $dimensions = $application->getTerminalDimensions();
        return $dimensions[0] ?: 120;
    }

}
