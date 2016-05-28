<?php

namespace TestNamespace;
use TestNamespace\SubNamespace\Test2;
use TestNamespace\TestInterface\TestIface;
use OtherNamespace;

class Test extends TestAbstract implements TestIface
{
    public function test(TestIface $class) {
        return new Test2();
    }

    public function abstractMethod(Test2Namespace\Test2SubNamespace\AClass $doctrine) {
        new OtherNamespace\Test3();
        return;
    }
}