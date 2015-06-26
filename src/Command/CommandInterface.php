<?php

namespace phparsenal\fastforward\Command;


interface CommandInterface
{
    /**
     * @return string
     */
    public function getName();

    public function run();
}