<?php

namespace FluentHttp\Headers;

class HeaderFactory
{

    public static function create($reference = null)
    {
        switch($reference) {
            case null: return new DefaultHeader();
        }

        throw new RuntimeException("Invalid header reference to create the instance.");
    }

}