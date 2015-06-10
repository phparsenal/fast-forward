<?php
namespace nochso\clilaunch\Model;

use nochso\ORM\Model;

class Bookmark extends Model
{
	protected static $_tableName = 'bookmark';

	/**
	 * Primary key of the bookmark
	 *
	 * @var int
	 */
	public $id;

	/**
	 * A short name by which you find the bookmark
	 *
	 * @var string
	 */
	public $shortcut = '';
	/**
	 * Describes what the bookmark is or does
	 *
	 * @var string
	 */
	public $description = '';
	/**
	 * The command to be execute
	 *
	 * @var string
	 */
	public $command = '';
	/**
	 * The amount of times this bookmark was opened
	 *
	 * @var int
	 */
	public $hit_count = 0;
	/**
	 * UTC timestamp
	 *
	 * @var int
	 */
	public $ts_created;

	/**
	 * Ensure ts_created is set
	 */
	public function save()
	{
		if ($this->ts_created == null) {
			$this->ts_created = time();
		}
		parent::save();
	}

	public function run()
	{
		global $batchPath;
		file_put_contents($batchPath, $this->command);
		$this->hit_count++;
		$this->save();
		echo "Running '" . $this->shortcut . "' for the " . ordinal($this->hit_count) . " time.\n";
		echo $this->command . "\n";
	}
}