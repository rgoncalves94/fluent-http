<?php

namespace FluentHttp;

use FluentHttp\Headers\Header;
use FluentHttp\Headers\DefaultHeader;
use FluentHttp\Method;
use Closure;
use RuntimeException;

/**
* Facade to do http requests
*/
class Http
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var string Verbose HTTP
     */
    private $method;
    /**
     * @var array data to stringfy into the request
     */
    private $data;
    /**
     * @var Closure
     */
    private $behavior;
    /**
     * @var FluentHttp\Headers\Header
     */
    private $headers;
    /**
     * @var mixed
     */
    private $parsedResponse;

    /**
     * @var boolean Enable request debug mode
     */
    private $enabeDebug = false;

    /**
     * Factory method of FluentHttp\Http class
     *
     * @param FluentHttp\Headers\Header $headers
     * @return FluentHttp\Http
     */
    public static function create(Header $headers = null)
    {
        if(empty($header))
            $header = new DefaultHeader();

        return new Http($headers);
    }

    public function __construct(Header $header)
    {
        $this->headers = $header;
    }

    /**
     * Set headers
     *
     * @param FluentHttp\Headers\Header $header
     * @return $this
     */
    public function header(Header $header)
    {
        $this->header = $header;
        
        return $this;
    }

    /**
     * Execute a request using GET Verb
     *
     * @param $string $url
     * @param array $data
     * @param Closure $behavior
     * @return $this
     */
    public function get($url, $data = array(), Closure $behavior = null)
    {
        if(!empty($data)) {
            $queryStr = http_build_query($data);

            if(!preg_match('/\?/', $url)) {
                $queryStr = '?' . $queryStr;
            } else if(!preg_match('/\?$/', $url)) {
                if(preg_match('/\&/', $url)) {
                    $queryStr = "&" . $queryStr;
                }
            }
            
            $url .= $queryStr;
        }
        
        $this->url = $url;
        $this->data = array();
        $this->method = Method::METHOD_GET;
        $this->behavior = $behavior;
                
        return $this;
    }

    /**
     * Execute a request using POST Verb
     *
     * @param $string $url
     * @param array $data
     * @param Closure $behavior
     * @return $this
     */
    public function post($url, $data, Closure $behavior = null)
    {
        $this->url = $url;
        $this->data = $data;
        $this->method = Method::METHOD_POST;
        $this->behavior = $behavior;
                
        return $this;
    }

    /**
     * Execute a request using PUT Verb
     *
     * @param $string $url
     * @param array $data
     * @param Closure $behavior
     * @return $this
     */
    public function put($url, $data, Closure $behavior = null)
    {
        $this->url = $url;
        $this->data = $data;
        $this->method = Method::METHOD_PUT;
        $this->behavior = $behavior;
                
        return $this;
    }

    /**
     * Execute a request using DELETE Verb
     *
     * @param $string $url
     * @param array $data
     * @param Closure $behavior
     * @return $this
     */
    public function delete($url, $data, Closure $behavior = null)
    {
        $this->url = $url;
        $this->data = $data;
        $this->method = Method::METHOD_DELETE;
        $this->behavior = $behavior;
                
        return $this;
    }

    /**
     * Return a raw response of request
     *
     * @return string
     */
    public function raw()
    {
        return $this->doRequest($this->url, $this->method, $this->data, $this->behavior);
    }

    /**
     * Subscribe a closure with behavior
     *
     * @param Closure $fn function with behavior
     * @return $this
     */
    public function subscribe(Closure $fn)
    {
        $this->parsedResponse = $fn($this->raw());

        return $this;
    }

    /**
     * Execute a CUrl and try to parse the response
     *
     * @return mixed
     */
    public function send()
    {
        $rawResponse = $this->raw();

        if(json_decode($rawResponse, 1) !== false)
            return json_decode($rawResponse, 1);

        if(json_decode(simplexml_load_string($rawResponse), 1) !== false)
            return json_decode(simplexml_load_string($rawResponse), 1);

        return html_entity_decode($rawResponse);
    }

    /**
     * Enable debug mode
     *
     * @param boolean $status = true
     * @return $this
     */
    public function debug($status = true) {
        $this->enabeDebug = $status;

        return $this;
    }

    /**
     * Execute the request
     *
     * @param string $url
     * @param string $method default GET
     * @param array $data
     * @param Closure $behavior
     * @return string
     */
    protected function doRequest($url, $method = Method::METHOD_GET, $data = array(), Closure $behavior = null)
    {
        if($this->isValidMethod())
            

        $ch = curl_init();
        
        curl_setopt($ch,CURLOPT_URL, $url); 
        
           curl_setopt($ch,CURLOPT_HTTPHEADER, $this->headers->getHeaders()); 
        curl_setopt($ch,CURLOPT_TIMEOUT, 60); 
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        //curl_setopt($ch,CURLOPT_MAXREDIRS, $this->_maxRedirects); 
        //curl_setopt($ch,CURLOPT_FOLLOWLOCATION,$this->_followlocation); 
        //curl_setopt($ch,CURLOPT_COOKIEJAR,$this->_cookieFileLocation); 
        //curl_setopt($ch,CURLOPT_COOKIEFILE,$this->_cookieFileLocation); 

        if($method != Method::METHOD_GET)
            if(!empty($data))
                curl_setopt($ch,CURLOPT_POSTFIELDS, $data); 

        switch($method) {
            case Method::METHOD_GET:
            break;
            case Method::METHOD_POST:
                curl_setopt($ch,CURLOPT_POST, true); 
            break;
            case Method::METHOD_PUT:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, Method::METHOD_PUT);
            break;
            case Method::METHOD_DELETE:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, Method::METHOD_DELETE);
            break;
            default:
                throw new RuntimeException('Invalid Http Verbose method');
        }

        if($this->enabeDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, 1);

        if(!empty($behavior))
            $behavior($ch, $data);

        $rawResponse = curl_exec($ch);

        curl_close($ch);

        return $rawResponse;
    }

    /**
     * Validate method
     *
     * @param string $method
     * @return boolean
     */
    protected function isValidMethod($method)
    {
        return in_array($method, array(
            Method::METHOD_GET, Method::METHOD_POST, Method::METHOD_PUT, Method::METHOD_DELETE
        ));
    }

    /**
     * Validate url
     *
     * @param string $url
     * @return boolean
     */
    protected function isValidUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== FALSE;
    }

    /**
     * Validate data array
     *
     * @param array $data
     * @return boolean
     */
    protected function isValidData($data)
    {
        return is_array($data);
    }

}