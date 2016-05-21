<?php
namespace PhpDependencyManager\StringFilter;

class StringFilter
{
    const INVALID_SYMS = '/[^a-zA-Z0-9_]/';
    /**
     * @param $string
     * @param $pattern
     * @return mixed
     */
    public static function removeChars($string, $pattern)
    {
        if(!empty($string)&&!empty($pattern)) {
            return preg_replace($pattern, '', $string);
        }
    }

    /**
     * @param $objectName
     * @return mixed
     */
    public static function unifyObjectName($objectName)
    {
        return preg_replace('/[\/\\\\]/', '_', str_replace('-','', $objectName));
    }
}