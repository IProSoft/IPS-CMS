<?php

namespace ImcStream;

/**
 * @covers ImcStream\ImcStream
 */
class ImcStreamTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $existed = in_array('imc', stream_get_wrappers());
        if (!$existed) {
            stream_register_wrapper('imc', 'ImcStream\\ImcStream');
        }
    }

    public function testRegister()
    {
        $existed = in_array('imc', stream_get_wrappers());
        if ($existed) {
            stream_wrapper_unregister('imc');
        }
        ImcStream::register();
        $this->assertTrue(in_array('imc', stream_get_wrappers()));
    }

    /**
     * @expectedException ImcStream\Exception\IOException
     */
    public function testOpenInvalidLocalStream()
    {
        $arr = ['uri' => 'foo'];
        $str = serialize($arr);
        $fp = @fopen('imc://'.$str, 'r');
    }

    /**
     * @expectedException ImcStream\Exception\IOException
     */
    public function testOpenInvalidNetworkStream()
    {
        if (!ini_get('allow_url_fopen') || !$r = @fsockopen('8.8.8.8', 53, $e, $r, 1)) {
            $this->markTestSkipped('No internet connection or allow_url_fopen is not enabled.');
        }

        $arr = ['uri' => 'http://www.example.com/foo'];
        $str = serialize($arr);
        $fp = @fopen('imc://'.$str, 'r');
    }

    public function testSeekSeekableStream()
    {
        $arr = ['uri' => __DIR__.'/Fixtures/hello.txt', 'seek' => true];
        $str = serialize($arr);
        $fp = @fopen('imc://'.$str, 'r');

        fseek($fp, 6);
        $data = fread($fp, 5);
        $this->assertEquals('world', $data);

        fseek($fp, 0);
        $data = fread($fp, 7);
        $this->assertEquals('hello w', $data);

        fseek($fp, 0);
        $data = '';
        if (!feof($fp)) {
            $data .= fread($fp, 1024);
        }
        $this->assertEquals(1, preg_match('/\\Ahello world\\Z/', $data));

        fseek($fp, 1000);
        $data = fread($fp, 10);
        $this->assertEmpty($data);
        $this->assertTrue(feof($fp));
        @fclose($fp);
    }

    public function testSeekUnseekableStream()
    {
        if (!ini_get('allow_url_fopen') || !$r = @fsockopen('8.8.8.8', 53, $e, $r, 1)) {
            $this->markTestSkipped('No internet connection or allow_url_fopen is not enabled.');
        }
        @fclose($r);
        $arr = ['uri' => 'http://www.example.com', 'seek' => true];
        $str = serialize($arr);
        $fp = @fopen('imc://'.$str, 'r');
        $data1 = fread($fp, 1024);
        $data2 = fread($fp, 100);
        $data3 = fread($fp, 500);
        rewind($fp);
        $data4 = fread($fp, 100);
        $data5 = fread($fp, 500);
        $data6 = fread($fp, 1024);
        $this->assertEquals($data1.$data2.$data3, $data4.$data5.$data6);

        fseek($fp, 100000);
        $data = fread($fp, 10);
        $this->assertEmpty($data);
        $this->assertTrue(feof($fp));
        @fclose($fp);
    }

    /**
     * @depends testSeekUnseekableStream
     */
    public function testGlobal()
    {
        if (!ini_get('allow_url_fopen') || !$r = @fsockopen('8.8.8.8', 53, $e, $r, 1)) {
            $this->markTestSkipped('No internet connection or allow_url_fopen is not enabled.');
        }
        $arr = [
            'uri'    => 'http://www.example.com',
            'seek'   => true,
            'global' => true,
        ];
        $str = serialize($arr);
        $fp = fopen('imc://'.$str, 'rb');
        $str1 = fread($fp, 1024);
        fclose($fp);

        $fp = fopen('imc://'.$str, 'rb');
        $str2 = fread($fp, 1024);
        fclose($fp);

        $this->assertEquals($str1, $str2);
        ImcStream::fclose('imc://'.$str);

        $fp = fopen('imc://'.$str, 'rb');
        $str2 = fread($fp, 1024);
        fclose($fp);
        ImcStream::fclose();
    }

    /**
     * @expectedException ImcStream\Exception\IOException
     */
    public function testDataLimit()
    {
        if (!ini_get('allow_url_fopen') || !$r = @fsockopen('8.8.8.8', 53, $e, $r, 1)) {
            $this->markTestSkipped('No internet connection or allow_url_fopen is not enabled.');
        }
        $arr = [
            'uri'   => 'http://www.example.com',
            'data_limit' => 1,
        ];
        $str = serialize($arr);
        $fp = fopen('imc://'.$str, 'rb');
        fread($fp, 1024);
        fread($fp, 1024);
    }

    /**
     * @expectedException ImcStream\Exception\IOException
     */
    public function testTimeout()
    {
        if (!ini_get('allow_url_fopen') || !$r = @fsockopen('8.8.8.8', 53, $e, $r, 1)) {
            $this->markTestSkipped('No internet connection or allow_url_fopen is not enabled.');
        }
        $arr = [
            'uri' => 'http://www.example.com',
            'timeout'  => 0.000001,
        ];
        $str = serialize($arr);
        $fp = fopen('imc://'.$str, 'rb');
        fread($fp, 1024);
        fread($fp, 1024);
    }
}
