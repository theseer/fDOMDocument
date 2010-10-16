fDOMDocument
============

The classes contained within this repository extend the standard DOM to use exceptions at
all occasions of errors instead of PHP warnings or notices. They also add various custom methods
and shortcuts for convinience and to simplify the usage of DOM.

Requirements
------------

PHP: 5.3.0
Extensions: dom, libxml

Usage
-----

Simply require/include the autoload.php supplied and you can start using fDOMDocument as a 
drop in replacement for DOMDocument.

Usage Samples
--------------

<?php

 $dom = new fDOMDOcument();
 try {
   $dom->loadXML('<?xml version="1.0" ?><root><child name="foo" /></root>');
 } catch (fDOMException $e) {
   die($e);
 }

 $child = $dom->query('//child')->item(0);
 print_r($child->getAttribute('name'));
 print_r($child->getAttribute('missing','DefaultValue'));

?>
