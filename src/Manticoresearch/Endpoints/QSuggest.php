<?php
namespace Manticoresearch\Endpoints;

class QSuggest extends EmulateBySql
{
	protected array $params = [];

	public function setBody($params = null): self {
		if ($params !== null) {
			$query = addslashes($params['query'] ?? '');
			$table = $this->params['table'] ?? '';
			$limit = (int)($params['limit'] ?? 5);
			$sentence = isset($params['sentence']) ? (int)$params['sentence'] : null;
			$options = $params['options'] ?? [];

			if (!$table) {
				throw new \RuntimeException('Table name is required for QSuggest');
			}

			$sql = "CALL QSUGGEST('$query', '$table'";
			$hasOptions = $limit > 0 || $sentence !== null || !empty($options);
			if ($hasOptions) {
				$sql .= ',';
				$optionParts = [];
				if ($limit > 0) {
					$optionParts[] = " $limit AS limit";
				}
				if ($sentence !== null) {
					$optionParts[] = " $sentence AS sentence";
				}
				// Додаємо тільки відомі опції з $options
				if (!empty($options)) {
					if (isset($options['max_edits'])) {
						$maxEdits = (int)$options['max_edits'];
						$optionParts[] = " $maxEdits AS max_edits";
					}
					if (isset($options['word_dict']) && in_array($options['word_dict'], ['words', 'freq'])) {
						$optionParts[] = " '{$options['word_dict']}' AS word_dict";
					}
					if (isset($options['expansion_limit'])) {
						$expansionLimit = (int)$options['expansion_limit'];
						$optionParts[] = " $expansionLimit AS expansion_limit";
					}
					if (isset($options['max_matches'])) {
						$maxMatches = (int)$options['max_matches'];
						$optionParts[] = " $maxMatches AS max_matches";
					}
				}
				$sql .= implode(',', $optionParts);
			}
			$sql .= ')';

			return parent::setBody(['query' => $sql]);
		}
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
