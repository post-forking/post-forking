<?php 
/*
$original = '1
2
3';
$fork = '1
2
2.5
3';
$latest = '1
2
3
4';

foreach ( array( 'original', 'fork', 'latest' ) as $string )
	$$string = explode( "\n", $$string );

$diff = new Text_Diff3( $original, $fork, $latest );

foreach ( $diff->_edits as $edit )
	if ( $edit->isConflict() )
		echo "CONFLCIT";

var_dump ( $diff->getDiff() );

$text_diff = new Text_Diff( $latest, $fork );
		
var_dump(  $text_diff->getDiff() );

var_dump( $diff->mergedOutput() );

exit(); */