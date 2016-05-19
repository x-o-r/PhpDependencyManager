<?php

namespace Test2Namespace;

class TestFactory
{
    public static function getInstance()
    {
        return new TestDAO(new Doctrine());
    }
}