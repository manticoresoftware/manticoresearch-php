<?php


namespace Manticoresearch\Response;

use Manticoresearch\Response;

class Bulk extends Response
{

    /*
     * Check whenever response has error
     * @return bool
     */
    public function hasError()
    {
        $response = $this->getResponse();
        foreach ($response as $r) {
            if (isset($r['error']) && $r['error'] !== '') {
                return true;
            }
        }
        return false;
    }

    /*
     * Return error
     * @return false|string
     */
    public function getError()
    {
        $response = $this->getResponse();
        $errors = "";
        foreach ($response as $r) {
            if (isset($r['error']) && $r['error'] !== '') {
                $errors .= json_encode($r['error'], true);
            }
        }
        return $errors;
    }
}
