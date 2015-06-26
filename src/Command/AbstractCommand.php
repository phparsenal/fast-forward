<?php

namespace phparsenal\fastforward\Command;

use League\CLImate\CLImate;
use phparsenal\fastforward\Client;

abstract class AbstractCommand implements CommandInterface
{
    /**
     * @var Client
     */
    protected $client;

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
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->cli = $this->client->getCLI();
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