<?php

namespace TestNamespace;


class Test2 extends TestAbstract// implements TestIface
{
    public function test(TestIface $class) {
        return new Test();
    }

    public function abstractMethod() {
        return;
    }
}