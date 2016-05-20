<?php
/**
 * Created by PhpStorm.
 * User: VinZ
 * Date: 20/05/2016
 * Time: 16:46
 */

namespace PhpDependencyManager\FileParser\Visitors;


interface NodeDataExchangeInterface
{
    /**
     * @return mixed
     */
    public function getDTO();
}