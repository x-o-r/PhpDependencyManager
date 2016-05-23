<?php

namespace Test2Namespace\Test2SubNamespace\Test2SubSubNamespace\blabla;
use Test2Namespace\Test2SubNamespace\Doctrine;
use Test1Namespace\Test2SubNamespace\TestDAO;

class TestFactory
{
    public static function getInstance()
    {
        new Toto();
        return new TestDAO(new Doctrine());
    }
}