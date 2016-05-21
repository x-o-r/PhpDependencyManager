<?php

namespace TestNamespace\TestInterface;

interface TestUpperIface
{
    public function test(TestIface $class);
}

interface TestIface extends TestUpperIface
{
    public function test(TestIface $class);
}