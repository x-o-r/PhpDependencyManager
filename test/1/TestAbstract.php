<?php

namespace TestNamespace;

class TestAbstract implements TestIface
{
    public function abstractMethod() {}
    public function test(TestIface $class) {}
}