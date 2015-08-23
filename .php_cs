<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('vendor')
    ->in(__DIR__);

return Symfony\CS\Config\Config::create()
    ->fixers(array(
        // Use default symfony level but disable the following..
        // Concatenation should be used with at least one whitespace around.
        '-concat_without_spaces',
        // An empty line feed should precede a return statement.
        '-return',
        // A return statement wishing to return nothing should be simply "return".
        '-empty_return',
        // Phpdocs short descriptions should end in either a full stop, exclamation mark, or question mark.
        '-phpdoc_short_description',
        // Pre incrementation/decrementation should be used if possible.
        '-pre_increment',
        // A single space should be between cast and variable.
        '-spaces_cast',
    ))
    ->finder($finder);
