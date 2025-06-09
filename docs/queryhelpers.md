Query helpers
-------------

Available for Manticore Search 3.4 or above.

## Keywords Analysis

Returns the tokenized versions of words from an input string.
`table` is mandatory.
`body` is mandatory and requires the presence of `query` - a string with one or more words.
Optional settings can be passed via the `options` array. For a complete list of options, check [Manticore docs](https://manual.manticoresearch.com/Searching/Autocomplete#CALL-KEYWORDS).


        $params = [
            'table' => 'testrt',
            'body' => [
                'query'=>'myword',
                'options' => [
                    'stats' =>1,
                    'fold_lemmas' => 1
                ]
            ]
         ];
        $response = $client->keywords($params);
        
## Keyword suggestion

Returns suggestions for an input word (usually a misspelled word). Note that suggestions work only with tables that have infixing enabled (`min_infix_len`>1).
`table` is mandatory.
`body` is mandatory and requires the presence of `query` - a string with one or more words.
Optional settings can be passed via the `options` array. For a complete list of options, check [Manticore docs](https://manual.manticoresearch.com/Searching/Spell_correction#CALL-QSUGGEST,-CALL-SUGGEST).

        $params = [
            'table' => 'testrt',
            'body' => [
                'query'=>'brokn',
                'options' => [
                    'limit' =>5
                ]
            ]
         ];
        $response = $client->suggest($params);
        // or, for QSUGGEST
        $response = $client->qsuggest($params);

Note that the table must be created as a keyword dictionary with a minimum infix length, otherwise Manticore will return an error. To do this, pass the `settings` option in the body part of a table creation request as below.

    'settings' => [
             'dict' => 'keywords',
             'min_infix_len' => 2
         ]

## Query explain

Allows you to get the query transformation tree of a query without running it. This is useful for testing queries.

`table` is mandatory.
`body` is mandatory and requires the presence of `query` - a query string expression.

    $params = [
         'table'=>'movies',
         'body' =>[
              'query'=>'("star wars trilogy"/2) | (empire back)'
         ]
    ];
    $response = $client->explainQuery($params);   
<!-- proofread -->