<?php


namespace Manticoresearch;

trait Utils
{
    public static function escape($string): string
    {
        $return = '';
        $stringlen = strlen($string);
        for ($i = 0; $i < $stringlen; ++$i) {
            $char = $string[$i];
            $ord = ord($char);
            if ($char !== "'" && $char !== "\"" && $char !== '\\' && $ord >= 32 && $ord <= 126) {
                $return .= $char;
            } else {
                $return .= '\\x' . dechex($ord);
            }
        }
        return $return;
    }
}
