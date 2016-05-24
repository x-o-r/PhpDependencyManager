<?php

namespace TestNamespace\SubNamespace;

use TestNamespace\Test2 as Test4;
use TestNamespace\Test3;

class Test
{
    public function __construct()
    {
        new Test3();
        new Test4();
    }
}
