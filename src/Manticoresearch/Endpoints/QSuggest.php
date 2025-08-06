<?php
namespace Manticoresearch\Endpoints;

class QSuggest extends Suggest
{
	protected array $params = [];

	public function setBody($params = null): self {
		parent::setBody($params);
		$this->body['query'] = preg_replace('/^CALL SUGGEST/', 'CALL QSUGGEST', $this->body['query']);

		return $this;
	}

	public function setParams(array $params): self {
		$this->params = $params;
		return $this;
	}

	public function getParams(): array {
		return $this->params;
	}
}
