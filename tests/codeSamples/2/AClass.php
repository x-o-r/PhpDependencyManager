<?php

namespace Test2Namespace\Test2SubNamespace;

use Test1Namespace\Test1SubNamespace\Test1Interface;
use Test1Namespace\TestDAO;

class AClass extends TestDAO implements Test1Interface
{
    public function method(TestDAO $dao)
    {
    }
}