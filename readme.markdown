fDOMDocument
============

The classes contained within this repository extend the standard DOM to use exceptions at
all occasions of errors instead of PHP warnings or notices. They also add various custom methods
and shortcuts for convinience and to simplify the usage of DOM.

Requirements
------------

    PHP: 5.3.3 (5.3.0-5.3.2 had serious issues with spl stacked autoloaders)
    Extensions: dom, libxml

Usage
-----

Simply require/include the autoload.php supplied and you can start using fDOMDocument as a
drop in replacement for DOMDocument.

Usage Samples
-------------

    <?php

    $dom = new TheSeer\fDOM\fDOMDOcument();
    try {
	$dom->loadXML('<?xml version="1.0" ?><root><child name="foo" /></root>');
    } catch (fDOMException $e) {
	die($e);
    }

    $child = $dom->queryOne('//child');
    print_r($child->getAttribute('name'));
    print_r($child->getAttribute('missing','DefaultValue'));

    ?>
    
    
Changelog
---------
1.2.0   - Changed fException to be more compatible with standard exceptions
          by adding a switch to get full info by getMessage()
        - Merged setAttributes() and setAttributesNS() methods from Andreas
        - Fixed internal registerNamespace variable mixup
1.1.0   - Renamed files to mimic classname cases
        - Fixed inSameDocument to support DOMDocument as well as DOMNodes
        - Added fDOMXPath class providing queryOne(), qoute() and prepare()
        - Adjusted forwarders in fDOMDocument to make use of new object
        - Fixed various return values to statically return true for compatibility with original API
        - Applied Workaround to fix potential problems with lost references to instances of fDOMDocument
        - Support registerPHPFunctions
        - Bump Copyright
        - Added missing docblocks
        
1.0.2   - Indenting and typo fixes, minor bugfixes

1.0.1   - Bugfix: typehints corrected

1.0.0   - Initial release
