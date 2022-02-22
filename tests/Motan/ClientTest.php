<?php
namespace Motan;
define('DEFAULT_TEST_URL', 'motan2://127.0.0.1:9981/com.weibo.HelloMTService?group=motan-demo-rpc');

use Motan\Protocol\Message;
use const Motan\Protocol\MSG_TYPE_RESPONSE;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2019-01-09 at 00:43:01.
 */
class ClientTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Client
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $url = new URL(DEFAULT_TEST_URL);
        $url->setConnectionTimeOut(50000);
        $url->setReadTimeOut(50000);
        $this->object = new Client($url);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers Motan\Client::getEndPoint
     * @todo   Implement testGetEndPoint().
     */
    public function testGetEndPoint()
    {
        $params = "testmsg";
        $this->object->doCall('Hello', $params);
        $ep = $this->object->getEndPoint();
        $this->assertEquals($ep->getResponseHeader(), $this->object->getResponseHeader());
    }

    /**
     * @covers Motan\Client::getResponseHeader
     * @todo   Implement testGetResponseHeader().
     */
    public function testGetResponseHeader()
    {
        $params = "testmsg";
        $this->object->doCall('Hello', $params);
        $resp_header = $this->object->getResponseHeader();
        $this->assertEquals(0xF1F1, $resp_header->getMagic());
    }

    /**
     * @covers Motan\Client::getResponseMetadata
     * @todo   Implement testGetResponseMetadata().
     */
    public function testGetResponseMetadata()
    {
        $params = "testmsg";
        $this->object->doCall('Hello', $params);
        $rs = $this->object->getResponseMetadata();
        $this->assertNotNull($rs);
    }

    /**
     * @covers Motan\Client::getResponseException
     * @todo   Implement testGetResponseException().
     */
    public function testGetResponseException()
    {
        $this->object->doCall('HelloX', 222, 123, 124, ['string','arr']);
        $rs = $this->object->getResponseException();
        $this->assertEquals('{"errcode":500,"errmsg":"method HelloX is not found in provider.","errtype":1}', $rs);
    }

    /**
     * @covers Motan\Client::getResponse
     * @todo   Implement testGetResponse().
     */
    public function testGetResponse()
    {
        $params = "testmsg";
        $this->object->doCall('Hello', $params);
        $rs = $this->object->getResponse();
        $this->assertObjectHasAttribute('_type',$rs);
        $this->assertEquals(MSG_TYPE_RESPONSE, $rs->getType());
    }

    /**
     * @covers Motan\Client::doCall
     * @todo   Implement testDoCall().
     */
    public function testDoCall()
    {
        $params = "testmsg";
        $rs = $this->object->doCall('Hello', $params);
        $this->assertEquals("hello testmsg", $rs);
    }

    /**
     * @covers Motan\Client::__call
     * @todo   Implement test__call().
     */
    public function test__call()
    {
        $params = "testmsg";
        $rs = $this->object->Hello($params);
        $this->assertEquals("hello testmsg", $rs);
    }

    /**
     * @covers Motan\Client::multiCall
     * @todo   Implement testMultiCall().
     */
    public function testMultiCall()
    {
        $url_str1 = 'motan2://127.0.0.1:9981/com.weibo.HelloMTService?group=motan-demo-rpc&method=Hello';
        $url_str2 = 'motan2://127.0.0.1:9981/com.weibo.HelloMTService?group=motan-demo-rpc&method=HelloX';
        $url1 = new URL($url_str1);
        $url2 = new URL($url_str2);
        $rs = $this->object->multiCall([$url1, $url2], 'Hello', "testmsg");
        $this->assertEquals("hello testmsg", $rs[0]);
    }
}
