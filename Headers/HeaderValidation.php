<?php

namespace FluentHttp\Headers;

class HeaderValidation
{

    public static function validate(Header $headers)
    {
        if(empty($headers->getHeader()))
            return false;

        foreach($headers->getHeader() as $headerName => $headerValue) {
            return true;
        }
    }

}