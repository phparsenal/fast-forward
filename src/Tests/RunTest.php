<?php

namespace phparsenal\fastforward\Tests;

use phparsenal\fastforward\Client;
use phparsenal\fastforward\Model\Bookmark;
use Symfony\Component\Console\Output\StreamOutput;

class RunTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    private static $client;

    public function testEmptyRun()
    {
        $tester = $this->getTester('run');
        $tester->execute(array());
        $display = $tester->getDisplay();
        $this->assertRegExp("/You don't have any commands saved yet. Now showing/", $display);
        $this->assertRegExp('/Usage:\s*add/s', $display);
    }

    public function testExactRun()
    {
        $bm = $this->insertBookmark('shortcut');
        $tester = $this->getTester('run');
        $tester->execute(array('shortcut' => 'shortcut'));
        $display = $tester->getDisplay();
        $this->assertRegExp("/Running '" . $bm->shortcut . "' for the 1st time/", $display);
    }

    public function testNoMatches()
    {
        $this->insertBookmark('notempty');
        $tester = $this->getTester('run');
        $tester->execute(array('shortcut' => 'empty'));
        $display = $tester->getDisplay();
        $this->assertRegExp("/There are no bookmarks matching shortcut: 'empty'/", $display);
    }

    /**
     * @param $commandName
     *
     * @return CommandTester
     */
    private function getTester($commandName)
    {
        $tester = new CommandTester(self::$client->find($commandName));
        return $tester;
    }

    /**
     * @param string $shortcut
     *
     * @return Bookmark
     */
    private function insertBookmark($shortcut)
    {
        $bm = new Bookmark();
        $bm->shortcut = $shortcut;
        $bm->command = 'command for ' . $shortcut;
        $bm->description = 'description for ' . $shortcut;
        $bm->save();
        return $bm;
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        Bookmark::select()->delete();
    }

    /**
     * This method is called before the first test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function setUpBeforeClass()
    {
        $stream = new StreamOutput(fopen('php://memory', 'w', false));
        self::$client = new Client();
        self::$client->init($stream, ':memory:');
    }
}
