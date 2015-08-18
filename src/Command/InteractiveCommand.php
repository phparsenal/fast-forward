<?php

namespace phparsenal\fastforward\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
}
