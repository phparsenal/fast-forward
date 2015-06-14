<?php

namespace phparsenal\fastforward\Model;

use nochso\ORM\Model;
use nochso\ORM\Relation;

class BookmarkType extends Model
{
    protected $_tableName = 'bookmark_type';
    protected $_relations = array(
        'bookmarkList' => array(Relation::HAS_MANY, 'phparsenal\fastforward\Model\Bookmark')
    );

    /**
     * @var Relation|Bookmark[]
     */
    public $bookmarkList;

    #region Table columns
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $namespace;
    #endregion
}