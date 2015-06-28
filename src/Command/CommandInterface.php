<?php

namespace phparsenal\fastforward\Command;

interface CommandInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param array $argv
     */
    public function run($argv);
}
