fDOMDocument
============

The classes contained within this repository extend the standard DOM to use exceptions at
all occasions of errors instead of PHP warnings or notices. They also add various custom methods
and shortcuts for convenience and to simplify the usage of DOM.

Requirements
------------

    PHP: 5.3.3 (5.3.0-5.3.2 had serious issues with spl stacked autoloaders)
    Extensions: dom, libxml


Installation
------------
fDOMDocument can be installed using the PEAR Installer, the backbone of the PHP Extension and Application Repository that provides a distribution system for PHP packages.

Depending on your OS distribution and/or your PHP environment, you may need to install PEAR or update your existing PEAR installation before you can proceed with the instructions in this chapter.
``sudo pear upgrade PEAR`` usually suffices to upgrade an existing PEAR installation. The PEAR Manual explains how to perform a fresh installation of PEAR.

The following two commands are all that is required to install fDOMDocument using the PEAR Installer:

    sudo pear channel-discover pear.netpirates.net
    sudo pear install TheSeer/fDOMDocument

After the installation you can find the source files inside your local PEAR directory; the path is usually either
``/usr/share/pear/TheSeer/fDOMDocument`` (Fedora/Redhat) or ``/usr/lib/php/TheSeer/fDOMDocument`` (Debian/Ubuntu).


Usage
-----

Simply require/include the autoload.php supplied and you can start using fDOMDocument as a
drop in replacement for DOMDocument.

Usage Samples
-------------
    <?php

    require 'TheSeer/fDOMDocument/autoload.php';

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
#####Release 1.4.0
* Added XPathQuery helper object, allowing for a prepared statement alike API around XPath

#####Release 1.3.2
* Added __clone method to reset domxpath object when domdocument gets cloned (Thanks to Markus Ineichen for pointing it out)

#####Release 1.3.1
* PHP 5.3 compatibility: changed interal behavior for incompatible changes from PHP 5.3 to 5.4 (Thanks to Jens Graefe for pointing it out)

#####Release 1.3.0
* Added appendTextNode method (Thanks to Markus Ineichen)
* Added appendElement / appendElementNS to DOMDocument to support documentElement "creation" (Thanks to Markus Ineichen)
* Overwrite createElement / createElementNS to throw exception on error
* Removed fDOMFilter code: Unmaintained and broken in its current form
* Added (static) Flag for fDOMException to globally enable full exception message
* Added Unit tests

#####Release 1.2.4
* PHP 5.4 compatibilty: added support for optional options bitmask on additional methods

#####Release 1.2.3
* Cleanup code style to adhere coding standard
* Added entity support for Attributes
* Added phpcs file to make coding standard public

#####Release 1.2.2
* Fix Exception to not overwrite final methods of \Exception

#####Release 1.2.1
* Changed fDOMDocument to be no longer final, use lsb to lookup actual class in constructor.
  This should fix test/mock issues.

#####Release 1.2.0
* Changed fException to be more compatible with standard exceptions by adding a switch to get full info by getMessage()
* Merged setAttributes() and setAttributesNS() methods from Andreas
* Fixed internal registerNamespace variable mixup

#####Release 1.1.0
* Renamed files to mimic classname cases
* Fixed inSameDocument to support DOMDocument as well as DOMNodes
* Added fDOMXPath class providing queryOne(), qoute() and prepare()
* Adjusted forwarders in fDOMDocument to make use of new object
* Fixed various return values to statically return true for compatibility with original API
* Applied Workaround to fix potential problems with lost references to instances of fDOMDocument
* Support registerPHPFunctions
* Bump Copyright
* Added missing docblocks

#####Release 1.0.2
* Indenting and typo fixes, minor bugfixes

#####Release 1.0.1
* Bugfix: typehints corrected

#####Release 1.0.0
* Initial release
