<?php

namespace phparsenal\fastforward\Command;

use phparsenal\fastforward\Client;
use phparsenal\fastforward\Settings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * InteractiveCommand will ask for missing parameters when run interactively.
 */
class InteractiveCommand extends Command
{
    /**
     * Interacts with the user.
     *
     * This method is executed before the InputDefinition is validated.
     * This means that this is the only place where the command can
     * interactively ask for values of missing required arguments.
     *
     * @param InputInterface $input  An InputInterface instance
     * @param OutputStyle    $output An OutputInterface instance
     */
    protected function interact(InputInterface $input, OutputStyle $output)
    {
        if (!$this->getApplication()->getSetting(Settings::INTERACTIVE)) {
            return;
        }
        $definition = $this->getDefinition();
        foreach ($definition->getArguments() as $argument) {
            if ($input->getArgument($argument->getName()) === null) {
                $this->promptMissingArgument($input, $output, $argument);
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputStyle    $output
     * @param InputArgument  $argument
     */
    protected function promptMissingArgument(InputInterface $input, OutputStyle $output, $argument)
    {
        $hasArgument = false;
        while (!$hasArgument) {
            $answer = $output->ask($argument->getDescription(), $argument->getDefault());
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
}
