<?php


namespace Manticoresearch\Results;

use Manticoresearch\Response;

class PercolateDocsResultSet implements \Iterator, \Countable
{

	/** @var int The position of the iterator through the result set */
	protected $position = 0;

	/** @var Response */
	protected $response;

	protected $array = [];

	/** @var int|mixed Total number of results */
	protected $total = 0;

	protected $took;

	/** @var mixed Did the query time out? */
	protected $timed_out; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

	protected $profile;

	public function __construct($responseObj, $docs) {

		foreach ($docs as $doc) {
			$this->array[] = ['doc' => $doc, 'queries' => []];
		}
		$this->response = $responseObj;
		$response = $responseObj->getResponse();
		if (!isset($response['hits']['hits'])) {
			return;
		}

		$hits = $response['hits']['hits'];
		foreach ($hits as $query) {
			if (!isset($query['fields'], $query['fields']['_percolator_document_slot'])) {
				continue;
			}

			foreach ($query['fields']['_percolator_document_slot'] as $d) {
				if (!isset($this->array[$d - 1])) {
					continue;
				}

				$this->array[$d - 1]['queries'][] = $query;
			}
		}
	}

	#[\ReturnTypeWillChange]
	public function rewind() {
		$this->position = 0;
	}

	#[\ReturnTypeWillChange]
	public function current() {
		return new PercolateResultDoc($this->array[$this->position]);
	}

	#[\ReturnTypeWillChange]
	public function next() {
		$this->position++;
	}

	#[\ReturnTypeWillChange]
	public function valid() {
		return isset($this->array[$this->position]);
	}

	#[\ReturnTypeWillChange]
	public function key() {
		return $this->position;
	}

	public function getTotal() {
		return $this->total;
	}

	public function getTime() {
		return $this->took;
	}

	public function hasTimedout() {
		return $this->timed_out; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
	}

	/**
	 * @return Response
	 */
	public function getResponse() {
		return $this->response;
	}

	#[\ReturnTypeWillChange]
	public function count() {
		return sizeof($this->array);
	}

	public function getProfile() {
		return $this->profile;
	}
}
