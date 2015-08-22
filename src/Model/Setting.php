<?php

namespace phparsenal\fastforward\Model;

use nochso\ORM\Model;

class Setting extends Model
{
    protected static $_tableName = 'setting';
    protected static $_primaryKey = 'key';

    public $key;
    public $value;
}
