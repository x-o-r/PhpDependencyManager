<?php

namespace TestNamespace\SubNamespace;
use TestNamespace\Test;
use TestNamespace\TestInterface;
use TestNamespace\TestAbstract;
use Hal\Component\File\Finder;

class Test2 extends TestAbstract implements TestInterface\TestIface
{
    public function test(TestInterface\TestIface $class) {
        $finder = new Finder();
        return new Test();
    }

    public function abstractMethod() {
        return;
    }
}