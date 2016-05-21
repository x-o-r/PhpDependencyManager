<?php

namespace TestNamespace;


class Test extends TestAbstract// implements TestIface
{
    public function test(TestIface $class) {
        return new Test2();
    }

    public function abstractMethod() {
        new Toto();
        return;
    }
}