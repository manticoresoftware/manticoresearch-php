<?php


namespace Manticoresearch;


trait Utils
{
    public static function escape($string)
    {
        $return = '';
        for ($i = 0; $i < strlen($string); ++$i) {
            $char = $string[$i];
            $ord = ord($char);
            if ($char !== "'" && $char !== "\"" && $char !== '\\' && $ord >= 32 && $ord <= 126)
                $return .= $char;
            else
                $return .= '\\x' . dechex($ord);
        }
        return $return;
    }
}