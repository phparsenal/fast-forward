<?php

namespace phparsenal\fastforward\Command;

use phparsenal\fastforward\Client;
use phparsenal\fastforward\Console\ConsoleStyle;
use phparsenal\fastforward\Settings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * InteractiveCommand will ask for missing parameters when run interactively.
 */
class InteractiveCommand extends Command
{
    /**
     * @var ConsoleStyle
     */
    protected $out;

    /**
     * Interacts with the user.
     *
     * This method is executed before the InputDefinition is validated.
     * This means that this is the only place where the command can
     * interactively ask for values of missing required arguments.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$this->getApplication()->getSetting(Settings::INTERACTIVE)) {
            return;
        }
        $definition = $this->getDefinition();
        foreach ($definition->getArguments() as $argument) {
            if ($input->getArgument($argument->getName()) === null) {
                $this->promptMissingArgument($input, $argument);
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param InputArgument  $argument
     */
    protected function promptMissingArgument(InputInterface $input, $argument)
    {
        $hasArgument = false;
        while (!$hasArgument) {
            $answer = $this->out->ask($argument->getDescription(), $argument->getDefault());
            if ($answer !== null) {
                $hasArgument = true;
                $input->setArgument($argument->getName(), $answer);
            } elseif (!$argument->isRequired()) {
                $hasArgument = true;
            }
        }
    }

    /**
     * Gets the application instance for this command.
     *
     * This is to make sure we get hinting for Client, not just Application.
     *
     * @return Client An Application instance
     */
    public function getApplication()
    {
        return parent::getApplication();
    }

    /**
     * Initializes the command just after the input has been validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if ($output instanceof ConsoleStyle) {
            $this->out = $output;
        } else {
            $this->out = new ConsoleStyle($input, $output);
        }
    }

    /**
     * Returns data passed via stdin.
     *
     * @return string
     */
    protected function getStdin()
    {
        $h = fopen('php://stdin', 'r');
        $data = stream_get_contents($h);
        fclose($h);
        return $data;
    }
}
