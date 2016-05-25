<?php

namespace Test1Namespace\Test2SubNamespace;
use Test1Namespace\TestDAO;

interface Test2Interface
{
    public function method(TestDAO $dao);
}