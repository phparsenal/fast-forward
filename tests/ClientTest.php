<?php
namespace phparsenal\fastforward;

define("DS", DIRECTORY_SEPARATOR);
define("ROOT", dirname(dirname(__FILE__)));

use cli\Streams;
use nochso\ORM\DBA\DBA;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Setup
     */
    protected function setUp()
    {
        require_once(ROOT . DS . 'src' . DS . 'Client.php');
        require_once(ROOT . DS . 'vendor' . DS . 'autoload.php');
        $this->client = new Client();
    }

    /**
     * Destroy it
     */
    protected function tearDown()
    {
        $this->client = null;
    }

    /**
     * Test with a number, should return a string
     */
    public function testOrdinalNumber()
    {
        $result = $this->client->ordinal(3);
        $this->assertInternalType('string', $result);
    }

    /**
     * Test with a letter
     */
    public function testOrdinalLetter()
    {
        $result = $this->client->ordinal('c');
        echo $result;
        $this->assertFalse($result);
    }
}