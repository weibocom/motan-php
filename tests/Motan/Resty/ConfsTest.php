<?php
namespace Motan\Resty;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2019-01-09 at 00:43:03.
 */
class ConfsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Confs
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp() : void
    {
        $this->markTestSkipped('Just Skip this.');
        $url = "motan2://127.0.0.1:9981/com.weibo.HelloMTService?group=motan-demo-rpc";
        $this->object = new Confs($url);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown() : void
    {
        parent::tearDown();
    }

    /**
     * @covers Motan\Resty\Confs::getRestyUrlInfo
     * @todo   Implement testGetRestyUrlInfo().
     */
    public function testGetRestyUrlInfo()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Motan\Resty\Confs::getReqParams
     * @todo   Implement testGetReqParams().
     */
    public function testGetReqParams()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Motan\Resty\Confs::getPath
     * @todo   Implement testGetPath().
     */
    public function testGetPath()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Motan\Resty\Confs::getService
     * @todo   Implement testGetService().
     */
    public function testGetService()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Motan\Resty\Confs::getSerialization
     * @todo   Implement testGetSerialization().
     */
    public function testGetSerialization()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Motan\Resty\Confs::getProtocol
     * @todo   Implement testGetProtocol().
     */
    public function testGetProtocol()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Motan\Resty\Confs::getGroup
     * @todo   Implement testGetGroup().
     */
    public function testGetGroup()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
