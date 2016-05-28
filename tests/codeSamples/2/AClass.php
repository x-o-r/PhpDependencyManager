<?php

namespace Test2Namespace\Test2SubNamespace;

use Test1Namespace\Test2SubNamespace\Test2Interface;
use Test1Namespace\TestDAO;

class AClass extends TestDAO implements Test2Interface
{
    public function method(TestDAO $dao)
    {
    }
}