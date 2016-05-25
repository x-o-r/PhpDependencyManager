<?php

namespace Test3Namespace;

class B_BClass extends BClass
{
    public function __construct()
    {
        new A_AClass();
    }
}