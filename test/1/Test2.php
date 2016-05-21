<?php

namespace TestNamespace\SubNamespace;


class Test2 extends TestAbstract// implements TestIface
{
    public function test(TestIface $class) {
        return new Testname();
    }

    public function abstractMethod() {
        return;
    }
}