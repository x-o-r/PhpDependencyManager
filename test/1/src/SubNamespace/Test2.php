<?php

namespace TestNamespace\SubNamespace;
use TestNamespace\Test;
use TestNamespace\TestInterface\TestIface;
use TestNamespace\TestAbstract;
use Hal\Component\File\Finder;

class Test2 extends TestAbstract implements TestIface
{
    public function test(TestIface $class) {
        $finder = new Finder();
        return new Test();
    }

    public function abstractMethod() {
        return;
    }
}