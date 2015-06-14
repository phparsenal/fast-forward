<?php
namespace phparsenal\fastforward;


class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Setup
     */
    protected function setUp()
    {
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

    /**
     * Test getBatchPath length
     */
    public function testGetBatchPathLength()
    {
        $result = $this->client->getBatchPath();
        $len = strlen($result);
        $this->assertGreaterThan(0, $len);
    }

    /**
     * Test getBatchPath type
     */
    public function testGetBatchPatchType()
    {
        $result = $this->client->getBatchPath();
        $this->assertInternalType('string', $result);
    }
}