<?php

namespace phparsenal\fastforward\Command;

abstract class AbstractCommand implements CommandInterface
{
    /**
     * @var \League\CLImate\CLImate
     */
    private $cli;

    /**
     * @var string
     */
    private $name;

    /**
     * This name is used for invoking the command.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


}