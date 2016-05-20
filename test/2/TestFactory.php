<?php

namespace Test2Namespace\Test2SubNamespace\Test2SubSubNamespace;

class TestFactory
{
    public static function getInstance()
    {
        new Toto();
        return new TestDAO(new Doctrine());
    }
}