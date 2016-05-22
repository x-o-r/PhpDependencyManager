<?php

namespace TestNamespace;
use TestNamespace\SubNamespace\Test2;
use OtherNamespace;
require __DIR__ . '/../vendor/autoload.php';

class Test extends TestAbstract implements TestIface
{
    public function test(TestIface $class) {
        return new Test2();
    }

    public function abstractMethod() {
        new OtherNamespace\Test3();
        return;
    }
}