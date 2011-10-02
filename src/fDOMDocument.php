<?php
/**
 * Copyright (c) 2010-2011 Arne Blankerts <arne@blankerts.de>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 *   * Neither the name of Arne Blankerts nor the names of contributors
 *     may be used to endorse or promote products derived from this software
 *     without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT  * NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER ORCONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 *
 * @category  PHP
 * @package   TheSeer\fDOM
 * @author    Arne Blankerts <arne@blankerts.de>
 * @copyright Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://github.com/theseer/fdomdocument
 *
 */

namespace TheSeer\fDOM {

    /**
     * fDOMDocument extension to PHP's DOMDocument.
     * This class adds various convenience methods to simplify APIs
     * It is set to final since further extending it would even more
     * break the Object structure after use of registerNodeClass.
     *
     * @category  PHP
     * @package   TheSeer\fDOM
     * @author    Arne Blankerts <arne@blankerts.de>
     * @access    public
     *
     */
    class fDOMDocument extends \DOMDocument {

        /**
         * XPath Object instance
         *
         * @var fDOMXPath
         */
        private $xp = null;

        /**
         * List of registered prefixes and their namespace uri
         * @var Array
         */
        private $prefixes = array();

        /**
         * Extended DOMDocument constructor
         *
         * @param string $version       XML Version, should be 1.0
         * @param string $encoding      Encoding, defaults to utf-8
         * @param array  $streamOptions optional stream options array
         *
         * @return fDOMDocument
         */
        public function __construct($version = '1.0', $encoding = 'utf-8', $streamOptions = null) {
            if (!is_null($streamOptions)) {
                $this->setStreamContext($streamOptions);
            }

            libxml_use_internal_errors(true);
            $rc = parent::__construct($version, $encoding);

            $this->registerNodeClass('\DOMDocument', get_called_class());
            $this->registerNodeClass('\DOMNode', 'TheSeer\fDOM\fDOMNode');
            $this->registerNodeClass('\DOMElement', 'TheSeer\fDOM\fDOMElement');
            $this->registerNodeClass('\DOMDocumentFragment', 'TheSeer\fDOM\fDOMDocumentFragment');

            return $rc;
        }

        /**
         * Set Stream context options
         *
         * @param Array $options Stream context options
         *
         * @return boolean true on success, false on failure
         */
        public function setStreamContext(Array $options) {
            if (!count($options)) {
                return false;
            }
            $context = stream_context_create($options);
            libxml_set_streams_context($context);
            return true;
        }

        /**
         * Wrapper to DOMDocument load with exception handling
         * Returns true on success to satisfy the compatibilty of the original DOM Api
         *
         * @param string  $fname   File to load
         * @param integer $options LibXML Flags to pass
         *
         * @return boolean
         */
        public function load($fname, $options = LIBXML_NONET) {
            $this->xp = null;
            $tmp = @parent :: load($fname, $options);
            if (!$tmp) {
                throw new fDOMException("loading file '$fname' failed.", fDOMException::LoadError);
            }
            return true;
        }

        /**
         * Wrapper to DOMDocument loadXML with exception handling
         * Returns true on success to satisfy the compatibilty of the original DOM Api
         *
         * @param string  $source  XML source code
         * @param integer $options LibXML option flags
         *
         * @return boolean
         */
        public function loadXML($source, $options = LIBXML_NONET) {
            $this->xp = null;
            $tmp = @parent :: loadXML($source, $options);
            if (!$tmp) {
                throw new fDOMException('parsing string failed', fDOMException::ParseError);
            }
            return true;
        }

        /**
         * Wrapper to DOMDocument loadHTMLFile with exception handling.
         * Returns true on success to satisfy the compatibilty of the original DOM Api
         *
         * @param string  $fname html file to load
         * @param integer $options Options bitmask (@see DOMDocument::loadHTMLFile)
         *
         * @return boolean
         */
        public function loadHTMLFile($fname, $options = NULL) {
            $this->xp = null;
            $tmp = @parent :: loadHTMLFile($fname, $options);
            if (!$tmp) {
                throw new fDOMException("loading html file '$fname' failed", fDOMException::LoadError);
            }
            return true;
        }

        /**
         * Wrapper to DOMDocument loadHTML with exception handling
         * Returns true on success to satisfy the compatibilty of the original DOM Api
         *
         * @param string  $source html source code
         * @param integer $options Options bitmask (@see DOMDocument::loadHTML)
         *
         * @return boolean
         */
        public function loadHTML($source, $options = NULL) {
            $this->xp = null;
            $tmp = @parent :: loadHTML($source, $options);
            if (!$tmp) {
                throw new fDOMException('parsing html string failed', fDOMException::ParseError);
            }
            return true;
        }

        /**
         * Wrapper to DOMDocument::save with exception handling
         *
         * @param string  $fname   filename to save to
         * @param integer $options Options bitmask (@see DOMDocument::save)
         *
         * @return integer bytes saved
         */
        public function save($filename, $options = NULL) {
            $tmp = @parent::save($filename, $options);
            if (!$tmp) {
                throw new fDOMException('saving xml file failed', fDOMException::SaveError);
            }
            return $tmp;
        }

        /**
         * Wrapper to DOMDocument::saveHTML with exception handling
         *
         * @return string html content
         */
        public function saveHTML() {
            $tmp = @parent::saveHTML();
            if (!$tmp) {
                throw new fDOMException('serializing to HTML failed', fDOMException::SaveError);
            }
            return $tmp;
        }

        /**
         * Wrapper to DOMDocument::saveHTMLfile with exception handling
         *
         * @param string $fname filename to save to
         * @param integer $options Options bitmask (@see DOMDocument::saveHTMLFile)
         *
         * @return integer bytes saved
         */
        public function saveHTMLFile($filename, $options = NULL) {
            $tmp = @parent::saveHTMLFile($filename, $options);
            if (!$tmp) {
                throw new fDOMException('saving to HTML file failed', fDOMException::SaveError);
            }
            return $tmp;
        }

        /**
         * Wrapper to DOMDocument::saveXML with exception handling
         *
         * @param \DOMNode context  node to start serializing at
         * @param integer  options  options flags as bitmask
         *
         * @return string serialized XML
         */
        public function saveXML(\DOMNode $node = NULL, $options = NULL) {
            try {
                $tmp = @parent::saveXML($node, $options);
                if (!$tmp) {
                    throw new fDOMException('serializing to XML failed', fDOMException::SaveError);
                }
                return $tmp;
            } catch (\Exception $e) {
                if (!$e instanceof fDOMException) {
                    throw new fDOMException($e->getMessage(), fDOMException::SaveError, $e);
                }
                throw $e;
            }
        }

        /**
         * get Instance of DOMXPath Object for current DOM
         *
         * @return DOMXPath
         */
        public function getDOMXPath() {
            if (is_null($this->xp)) {
                $this->xp = new fDOMXPath($this);
            }
            if (!$this->xp) {
                throw new fDOMException('creating DOMXPath object failed.', fDOMException::NoDOMXPath);
            }
            return $this->xp;
        }

        /**
         * Convert a given DOMNodeList into a DOMFragment
         *
         * @param DOMNodeList $list The Nodelist to process
         * @param boolean     $move Signale if nodes are to be moved into fragment or not
         *
         * @return DOMDocumentFragment
         */
        public function nodeList2Fragment(\DOMNodeList $list, $move=false) {
            $frag = $this->createDocumentFragment();
            foreach($list as $node) {
                $frag->appendChild($move ? $node : $node->cloneNode(true));
            }
            return $frag;
        }

        /**
         * Perform an xpath query
         *
         * @param String   $q   query string containing xpath
         * @param DOMNode  $ctx (optional) Context DOMNode
         * @param boolean  $registerNodeNS  Register flag pass thru
         *
         * @return \DOMNodeList
         */
        public function query($q, \DOMNode $ctx = null, $registerNodeNS = true) {
            if (is_null($this->xp)) {
                $this->getDOMXPath();
            }
            return $this->xp->evaluate($q, $ctx, $registerNodeNS);
        }

        /**
         * Perform an xpath query and return only the 1st match
         *
         * @param String   $q   query string containing xpath
         * @param DOMNode  $ctx (optional) Context DOMNode
         * @param boolean  $registerNodeNS  Register flag pass thru
         *
         * @return fDOMNode
         */
        public function queryOne($q, \DOMNode $ctx = null, $registerNodeNS = true) {
            if (is_null($this->xp)) {
                $this->getDOMXPath();
            }
            return $this->xp->queryOne($q, $ctx, $registerNodeNS);
        }


        /**
         * Forwarder to fDOMXPath's prepare method allowing for easy and secure
         * placeholder replacement comparable to sql's prepared statements
         * .
         * @param string $xpath    String containing xpath with :placeholder markup
         * @param array  $valueMap Array containing keys (:placeholder) and value pairs to be quoted
         */
        public function prepareQuery($xpath, array $valueMap) {
            if (is_null($this->xp)) {
                $this->getDOMXPath();
            }
            return $this->xp->prepare($xpath, $valueMap);
        }

        /**
         * Forward to DOMXPath->registerNamespace()
         *
         * @param string $prefix The prefix to use
         * @param string $uri    The uri to assign to this prefix
         *
         * @return void
         */
        public function registerNamespace($prefix, $uri) {
            if (is_null($this->xp)) {
                $this->getDOMXPath();
            }
            if (!$this->xp->registerNamespace($prefix, $uri)) {
                throw new fDOMException("Registering namespace '$uri' with prefix '$prefix' failed.", fDOMException::RegistrationFailed);
            }
            $this->prefixes[$prefix] = $uri;
        }

        /**
         * Forward to DOMXPath->registerPHPFunctions()
         *
         * @param mixed $restrict Array of function names or string with functionname to restrict callabilty to
         *
         * @return void
         */
        public function registerPHPFunctions($restrict = null) {
            if (is_null($this->xp)) {
                $this->getDOMXPath();
            }
            $this->xp->registerPHPFunctions($restrict);
            if (libxml_get_last_error()) {
                throw new fDOMException("Registering php functions failed.", fDOMException::RegistrationFailed);
            }
        }

        /**
         * Create a new element in namespace defined by given prefix
         *
         * @param $prefix   Namespace prefix for node to create
         * @param $name     Name of not element to create
         * @param $content  Optional content to be set
         *
         * @return fDOMElement Reference to created fDOMElement
         */
        public function createElementPrefix($prefix, $name, $content = null) {
            if (!isset($this->prefixes[$prefix])) {
                throw new fDOMException("'$prefix' not bound", fDOMException::UnboundPrefix);
            }
            $node = $this->createElementNS($this->prefixes[$prefix], $prefix.':'.$name);
            if (!is_null($content)) {
                $node->nodeValue = $content;
            }
            return $node;
        }

        /**
         * Check if the given node is in the same document
         *
         * @param \DOMNode $node Node to compare with
         *
         * @return boolean true on match, false if they differ
         *
         */
        public function inSameDocument(\DOMNode $node) {
            if ($node instanceof \DOMDocument) {
                return $this->isSameNode($node);
            }
            return $this->isSameNode($node->ownerDocument);
        }

    } // fDOMDocument

}
