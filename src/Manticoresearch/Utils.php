<?php


namespace Manticoresearch;

trait Utils
{
    public static function escape($string): string
    {
        $from = ['\\', '\\\'', '(',')','|','-','!','@','~','"','&', '/', '^', '$', '=', '<'];
        $to = ['\\\\', "'", '\(','\)','\|','\-','\!','\@','\~','\"', '\&', '\/', '\^', '\$', '\=', '\<'];
        return str_replace($from, $to, $string);
    }
}
