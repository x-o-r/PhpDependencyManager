<?php

namespace TestNamespace\SubNamespace;

use TestNamespace\Test2;
use TestNamespace\Test3;
use OtherNamespace\Test3 as Test4;

class Test
{
    public function __construct()
    {
        new Test2();
        new Test4();
    }
}