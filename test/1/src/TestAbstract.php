<?php

namespace TestNamespace;
use TestNamespace\TestInterface\TestIface;

class TestAbstract implements TestIface
{
    public function abstractMethod() {}
    public function test(TestIface $class) {}
}