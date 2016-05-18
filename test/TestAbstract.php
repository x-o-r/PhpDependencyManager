<?php
/**
 * Created by PhpStorm.
 * User: jhdw2952
 * Date: 18/05/2016
 * Time: 10:33
 */

namespace TestNamespace;


class TestAbstract implements TestIface
{
    public function abstractMethod() {}
    public function test(TestIface $class) {}
}