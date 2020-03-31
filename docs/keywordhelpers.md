Keyword helpers
--------------

Available for  Manticore Search 3.4 or above.

## Keywords Analysis

Return the tokenized versions of words from an input string.
`index` is mandatory.
`body` is mandatory and requires present of `query` - a string with one or more words.
Optional settings can be passed via `options` array.For complete list of options check [Manticore docs] (https://docs.manticoresearch.com/latest/html/sphinxql_reference/call_keywords_syntax.html).

        $params = [
            'index' => 'testrt',
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

Returns suggestions for an input word (usually a misspelled word). Note that suggestions work only with indexes with infixing enabled (`min_infix_len`>1).
`index` is mandatory.
`body` is mandatory and requires present of `query` - a string with one or more words.
Optional settings can be passed via `options` array. For complete list of options check [Manticore docs] (https://docs.manticoresearch.com/latest/html/sphinxql_reference/call_qsuggest_syntax.html).

        $params = [
            'index' => 'testrt',
            'body' => [
                'query'=>'brokn',
                'options' => [
                    'limit' =>5
                ]
            ]
         ];
        $response = $client->suggest($params);
        