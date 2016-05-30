<?php

namespace Test4Namespace;
use Test4Namespace\SubTest4Namespace\C_Class;

class C_CClass extends C_Class
{
    public function __construct()
    {
        new D_DClass();
    }
}