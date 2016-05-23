<?php

namespace TestNamespace\SubNamespace;

require __DIR__ . '/../vendor/autoload.php';


use TestNamespace\Test2 as Test4;
use TestNamespace\Test2;
//use TestNamespace\SubNamespace\Test2;

class Test
{
    public function __construct()
    {
        new Test2();
        new Test4();
    }
}

new Test();