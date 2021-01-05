<?php

// @codingStandardsIgnoreFile

namespace Manticoresearch\Query;

trigger_error(
    'manticoresearch-php: The Match class is deprecated as "match" is a reserved keyword in PHP 8.0, use MatchQuery instead.',
    \E_USER_DEPRECATED
);

/*
 * @deprecated since 1.6, use MatchQuery class.
 */

class Match extends MatchQuery
{

}
