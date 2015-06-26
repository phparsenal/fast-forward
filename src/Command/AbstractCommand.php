<?php

namespace phparsenal\fastforward\Command;

use League\CLImate\CLImate;

abstract class AbstractCommand implements CommandInterface
{
    /**
     * @var \League\CLImate\CLImate
     */
    protected $cli;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param CLImate $cli
     */
    public function __construct(CLImate $cli)
    {
        $this->cli = $cli;
    }

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