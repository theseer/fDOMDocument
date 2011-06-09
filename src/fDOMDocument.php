<?php
/**
 * Copyright (c) 2010 Arne Blankerts <arne@blankerts.de>
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
    * fDOMDocument
    *
    * @category  PHP
    * @package   TheSeer\fDOM
    * @author    Arne Blankerts <arne@blankerts.de>
    * @access    public
    *
    */
   final class fDOMDocument extends \DOMDocument {

      /**
       * XPath Object instance
       *
       * @var \DOMXPath
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
       *
       * @param string  $fname   File to load
       * @param integer $options LibXML Flags to pass
       *
       * @return void
       */
      public function load($fname, $options = LIBXML_NONET) {
         $this->xp = null;
         $tmp = parent :: load($fname, $options);
         if (!$tmp) {
            throw new fDOMException("loading file '$fname' failed.", fDOMException::LoadError);
         }
      }

      /**
       * Wrapper to DOMDocument loadXML with exception handling
       *
       * @param string  $source  XML source code
       * @param integer $options LibXML option flags
       *
       * @return mixed if called statically fDOMDocument otherwise boolean true
       */
      public function loadXML($source, $options = LIBXML_NONET) {
         $this->xp = null;
         $tmp = @parent :: loadXML($source, $options);
         if (!$tmp) {
            throw new fDOMException('parsing string failed', fDOMException::ParseError);
         }
      }

      /**
       * Wrapper to DOMDocument loadHTMLFile with exception handling
       *
       * @param string $fname html file to load
       *
       * @return mixed if called statically fDOMDocument otherwise boolean true
       */
      public function loadHTMLFile($fname) {
         $this->xp = null;
         $tmp = @parent :: loadHTMLFile($fname);
         if (!$tmp) {
            throw new fDOMException("loading html file '$fname' failed", fDOMException::LoadError);
         }
      }

      /**
       * Wrapper to DOMDocument loadHTML with exception handling
       *
       * @param string $source html source code
       *
       * @return mixed if called statically fDOMDocument otherwise boolean true
       */
      public function loadHTML($source) {
         $this->xp = null;
         $tmp = @parent :: loadHTML($source);
         if (!$tmp) {
            throw new fDOMException('parsing html string failed', fDOMException::ParseError);
         }
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
       * Wrapper to DOMDocument::saveHTMLfile with exception handling
       *
       * @param string $fname filename to save to
       *
       * @return integer bytes saved
       */
      public function saveXML(\DOMNode $node = NULL, $options = NULL) {
         try {
            $tmp = @parent::saveXML($node, $options);
            if (!$tmp) {
               throw new fDOMException('serializing to XML failed', fDOMException::SaveError);
            }
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
            $this->xp = new \DOMXPath($this);
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
       * @param String  $q   query string containing xpath
       * @param DOMNode $ctx (optional) Context DOMNode
       *
       * @return unknown
       */
      public function query($q, \DOMNode $ctx = null) {
         if (is_null($this->xp)) {
            $this->getDOMXPath();
         }
         libxml_clear_errors();
         $rc = $this->xp->evaluate($q, ($ctx instanceof \DOMNode) ? $ctx : $this->documentElement);
         if (libxml_get_last_error()) {
            throw new fDOMException('evaluating xpath expression failed.', fDOMException::QueryError);
         }
         return $rc;
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
         $this->prefix[$prefix] = $uri;
      }


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

   } // fDOMDocument

}