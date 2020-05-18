<?php


namespace Manticoresearch\Results;

use Manticoresearch\ResultHit;

class PercolateResultHit extends ResultHit
{
    public function getDocSlots()
    {
        return $this->data['fields']['_percolator_document_slot'];
    }

    public function getDocsMatched($docs)
    {
        return array_map(function ($v) use ($docs) {
            return $docs[$v - 1];
        }, $this->data['fields']['_percolator_document_slot']);
    }
}
