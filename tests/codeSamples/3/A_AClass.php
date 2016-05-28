<?php

namespace Test3Namespace;

class A_AClass extends AClass
{
    public function __construct()
    {
        new B_BClass();
    }
}