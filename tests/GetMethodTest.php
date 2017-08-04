<?php

namespace Tests\FluentHttp;

use PHPUnit\Framework\TestCase;
use FluentHttp\Http;
use FluentHttp\Headers\HeaderFactory;
use Tests\FluentHttp\TestUrls;
use ReflectionClass;

/**
 * @covers FluentHttp\Http
 */
final class GetMethodTest extends TestCase
{

    public function testGETMethodQueryStringCreation()
    {
        $http = Http::create()->get(TestUrls::JSON_URL, array(
            "hi" => "bye",
            "its" => "working"
        ));

        $rc = new ReflectionClass($http);

        $rp = $rc->getProperty('url');
        $rp->setAccessible(true);
        $result = $rp->getValue($http);

        $this->assertEquals(
            $result,
            TestUrls::JSON_URL . "?hi=bye&its=working"
        );
    }

    public function testGETMethodQueryStringCreationMultidimensionalArray()
    {
        $http = Http::create()->get(TestUrls::JSON_URL, array(
            "hi" => "bye",
            "more" => array("than" => "words")
        ));

        $rc = new ReflectionClass($http);

        $rp = $rc->getProperty('url');
        $rp->setAccessible(true);
        $result = $rp->getValue($http);

        $this->assertEquals(
            $result,
            TestUrls::JSON_URL . "?hi=bye&more%5Bthan%5D=words"
        );
    }

    public function testGETMethodQueryStringCreationWithParamSeparator()
    {
        $http = Http::create()->get(TestUrls::JSON_URL . '?', array(
            "hi" => "bye",
            "more" => array("than" => "words")
        ));

        $rc = new ReflectionClass($http);

        $rp = $rc->getProperty('url');
        $rp->setAccessible(true);
        $result = $rp->getValue($http);

        $this->assertEquals(
            $result,
            TestUrls::JSON_URL . "?hi=bye&more%5Bthan%5D=words"
        );
    }

    public function testGETMethodQueryStringCreationWithExistingParameters()
    {
        $http = Http::create()->get(TestUrls::JSON_URL . '?here=has+a+parameter&', array(
            "hi" => "bye",
            "more" => array("than" => "words")
        ));

        $rc = new ReflectionClass($http);

        $rp = $rc->getProperty('url');
        $rp->setAccessible(true);
        $result = $rp->getValue($http);

        $this->assertEquals(
            $result,
            TestUrls::JSON_URL . "?here=has+a+parameter&hi=bye&more%5Bthan%5D=words"
        );
    }

    public function testGETMethodWithoutParameter()
    {
        $http = Http::create()->get(TestUrls::JSON_URL);

        $rc = new ReflectionClass($http);

        $rp = $rc->getProperty('url');
        $rp->setAccessible(true);
        $result = $rp->getValue($http);

        $this->assertEquals(
            $result,
            TestUrls::JSON_URL
        );
    }

    public function testGetMethodReceivingRawResponse()
    {
        $rawResponse = Http::create()->get(TestUrls::JSON_URL)->raw();

        $this->assertNotEmpty($rawResponse);
        $this->assertJsonStringEqualsJsonString(json_encode(
            array(
                "status" => true,
                "message" => "It's a json endpoint"
            )
        ), $rawResponse);
    }

    public function testGetMethodReceivingParsedResponse()
    {
        $response = Http::create()->get(TestUrls::JSON_URL)->run();

        $this->assertNotEmpty($response);
        $this->assertEquals(json_decode('{
  "userId": 1,
  "id": 1,
  "title": "sunt aut facere repellat provident occaecati excepturi optio reprehenderit",
  "body": "quia et suscipit\nsuscipit recusandae consequuntur expedita et cum\nreprehenderit molestiae ut ut quas totam\nnostrum rerum est autem sunt rem eveniet architecto"
}', true), $response);
    }

    public function testGetMethodReceivingParsedResponseWhenSubscribe()
    {
        $response = Http::create()
            ->get(TestUrls::JSON_URL)
            ->subscribe(function($json, $http) {
                return compact('json', 'http');
            });

        $this->assertArrayHasKey('json', $response);
        $this->assertArrayHasKey('http', $response);
    }
    
}