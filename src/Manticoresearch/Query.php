<?php


namespace Manticoresearch;

class Query implements Arrayable
{
	protected $params = [];

	public function add($k, $v) {
		$this->params[$k] = $v;
	}
	public function toArray() {
		return  $this->convertArray($this->params);
	}

	protected function convertArray($params) {

		$return = [];
		foreach ($params as $k => $v) {
			if ($v instanceof Arrayable) {
				$return[$k] = $v->toArray();
			} elseif (is_array($v)) {
				$return[$k] = $this->convertArray($v);
			} else {
				if ($v === null) {
					return null;
				}

				$return[$k] = $v;
			}
		}
		return $return;
	}
}
