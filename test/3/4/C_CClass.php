<?php

namespace Test4Namespace;

class C_CClass extends C_Class
{
    public function __construct()
    {
        new D_DClass();
    }
}