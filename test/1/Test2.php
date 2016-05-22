<?php

namespace TestNamespace;


class Test2
{
    public function __construct()
    {
        new Test3();
        new \TestNamespace\SubNamespace\Test2();
    }
}