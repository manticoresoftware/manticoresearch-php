<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
return $config
    ->setFinder($finder)
    ->setRules([
        '@PER-CS' => true,
    ]);
