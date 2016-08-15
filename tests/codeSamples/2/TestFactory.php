<?php

namespace Test2Namespace\Test2SubNamespace;
use Test1Namespace\TestDAO;

class TestFactory
{
    public static function getInstance()
    {
        new OutOfAnalyseClass();
        return new TestDAO(new AClass());
    }
}   