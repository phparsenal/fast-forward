<?php
use nochso\clilaunch\Model;

$dir = dirname($_SERVER['PHP_SELF']);
chdir($dir);
require 'vendor/autoload.php';


$batchPath = $dir . DIRECTORY_SEPARATOR . 'cli-launch.temp.bat';
file_put_contents($batchPath, '');


\nochso\ORM\DBA\DBA::connect('sqlite:./db.sqlite', '', '');

if ($argc > 1) {
    if ($argv[1] == "add") {
        addBookmark(array_slice($argv, 2));
    } else {
        runBookmark(array_slice($argv, 1));
    }
} else {
    runBookmark(array());
}

function addBookmark($args)
{
    $bookmark = new Model\Bookmark();
    $count = count($args);
    // Get as much as you can
    switch ($count) {
        case 3:
            $bookmark->command = $args[2];
        case 2:
            $bookmark->description = $args[1];
        case 1:
            $bookmark->shortcut = $args[0];
    }

    // Ask for what's left
    switch ($count) {
        case 0:
            $bookmark->shortcut = cli\Streams::prompt("Shortcut for easy searching");
        case 1:
            $bookmark->description = cli\Streams::prompt("The description of the command");
        case 2:
            $bookmark->command = cli\Streams::prompt("Command to be executed");
    }
    $bookmark->save();
    cli\Streams::out("New bookmark was saved: " . $bookmark->shortcut);
}

function runBookmark($args)
{
    $query = Model\Bookmark::select();
    foreach ($args as $arg) {
        $query->like('shortcut', $arg . '%');
    }
    $query->orderDesc('hit_count');
    $bookmarks = $query->all();
    $bm = selectBookmark($bookmarks, $args);
    if ($bm !== null) {
        $bm->run();
    }
}

function selectBookmark($bookmarks, $args)
{
    if (count($bookmarks) == 1) {
        /** @var Model\Bookmark $bm */
        $bm = $bookmarks->current();
        if ($bm->shortcut == $args[0]) {
            return $bm;
        }
    }

    $map = array();
    $i = 0;
    $table = new \cli\Table();
    $table->setHeaders(['#', 'Shortcut', 'Description', 'Command', 'Hits']);
    $rows = array();
    foreach ($bookmarks as $id => $bm) {
        $map[$i] = $id;
        $rows[] = array($i, $bm->shortcut, $bm->description, $bm->command, $bm->hit_count);
        $i++;
    }
    $table->setRows($rows);
    $r = new \cli\table\Ascii();
    $r->setCharacters(array(
        'corner' => '',
        'line' => '',
        'border' => ' ',
        'padding' => '',
    ));
    $table->setRenderer($r);
    $table->display();
    \cli\Streams::out("Which # do you want to run? ");
    $num = cli\Streams::input();
    if (isset($map[$num])) {
        return $bookmarks[$map[$num]];
    }
    return null;
}

function ordinal($number)
{
    $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
    if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
        return $number . 'th';
    } else {
        return $number . $ends[$number % 10];
    }
}