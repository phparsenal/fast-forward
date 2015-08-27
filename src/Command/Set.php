<?php

namespace phparsenal\fastforward\Command;

use phparsenal\fastforward\Model\Setting;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Save and list settings
 */
class Set extends InteractiveCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('set')
            ->setDescription('Set or get variables')
            ->addOption('list', 'l', InputOption::VALUE_NONE, 'Show a list of all current settings.')
            ->addOption('default', 'd', InputOption::VALUE_NONE,
                'Display a list of supported settings and their defaults.')
            ->addOption('import-file', 'f', InputOption::VALUE_REQUIRED, 'Import from the specified file or STDIN')
            ->addOption('import-stdin', 'i', InputOption::VALUE_NONE, 'Import setting via STDIN pipe')
            ->addArgument('key', InputArgument::OPTIONAL, 'Name or key of the setting')
            ->addArgument('value', InputArgument::OPTIONAL, 'Value to be set');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('list')) {
            $this->listAll($output);
            return;
        }
        $client = $this->getApplication();
        if ($input->getOption('default')) {
            $client->getSettings()->showSupportedSettings();
            return;
        }
        $importFile = $input->getOption('import-file');
        if ($importFile !== null) {
            $this->addLines($this->getLinesFile($importFile), $output);
            return;
        }
        if ($input->getOption('import-stdin')) {
            $this->addLines($this->getStdin(), $output);
            return;
        }
        $key = $input->getArgument('key');
        $value = $input->getArgument('value');
        if ($key !== null && $value !== null) {
            $client->setSetting($key, $value);
            return;
        }
        if ($key !== null) {
            $client->getSettings()->showSupportedSettings($key);
            return;
        }
        throw new \RuntimeException('Calling this command without arguments can only be done interactively.');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('list')
            || $input->getOption('import-stdin')
            || $input->getOption('import-file') !== null
        ) {
            return;
        }
        parent::interact($input, $output);
    }

    /**
     * @param OutputInterface $output
     */
    public function listAll(OutputInterface $output)
    {
        $settings = Setting::select()->orderAsc('key')->all();
        foreach ($settings as $setting) {
            $output->writeln($setting->key . ' ' . $setting->value);
        }
    }

    /**
     * @param $filepath
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getLinesFile($filepath)
    {
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Could not find file '{$filepath}'");
        }
        $lines = file($filepath);
        return $lines;
    }

    /**
     * @param string|array $lines
     * @param OutputInterface $output
     */
    private function addLines($lines, OutputInterface $output)
    {
        if (!is_array($lines)) {
            $lines = explode("\n", $lines);
        }
        foreach ($lines as $line) {
            if (preg_match('/^([^ ]+) (.*)/', $line, $matches)) {
                $this->getApplication()->setSetting($matches[1], $matches[2]);
            } elseif (trim($line) !== '') {
                $output->writeln('Line ignored: ' . $line);
            }
        }
    }
}
