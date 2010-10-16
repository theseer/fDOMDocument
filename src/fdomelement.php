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
    * fDomElement
    *
    * @category  PHP
    * @package   TheSeer\fDOM
    * @author    Arne Blankerts <arne@blankerts.de>
    * @access    public
    *
    */
   class fDOMElement extends \DOMElement {

      /**
       * Forward to fDomDocument->query()
       *
       * @param string    $q   XPath to use
       * @param \DOMNode  $ctx \DOMNode to overwrite context
       *
       * @return DomNodeList
       */
      public function query($q, \DOMNode $ctx = null) {
         return $this->ownerDocument->query($q, $ctx ? $ctx : $this);
      }

      /**
       * Parse and append XML String to node
       *
       * @param String $str string to process
       *
       * @return fDomDocumentFragment Reference to the created Fragment
       */
      public function appendXML($str) {
         $frag = $this->ownerDocument->createDocumentFragment();
         $frag->appendXML($str);
         $this->appendChild($frag);
         return $frag;
      }

      /**
       * Create a new element and append it
       *
       * @param $name     Name of not element to create
       * @param $content  Optional content to be set
       *
       * @return fDOMElement Reference to created fDOMElement
       */
      public function appendElement($name, $content = null) {
         $node = $this->ownerDocument->createElement($name, $content);
         $this->appendChild($node);
         return $node;
      }

      /**
       * Create a new element in given namespace and append it
       *
       * @param $ns       Namespace of node to create
       * @param $name     Name of not element to create
       * @param $content  Optional content to be set
       *
       * @return fDOMElement Reference to created fDOMElement
       */
      public function appendElementNS($ns, $name, $content = null) {
         $node = $this->ownerDocument->createElementNS($ns, $name, $content);
         $this->appendChild($node);
         return $node;
      }

      /**
       * Create a new element in given namespace and append it
       *
       * @param $prefix   Namespace prefix for node to create
       * @param $name     Name of not element to create
       * @param $content  Optional content to be set
       *
       * @return fDOMElement Reference to created fDOMElement
       */
      public function appendElementPrefix($prefix, $name, $content = null) {
         $node = $this->ownerDocument->createElementPrefix($prefix, $name, $content);
         $this->appendChild($node);
         return $node;
      }

      /**
       * Wrapper to DomElement->getAttribute with default value option
       *
       * Note: A set but emptry attribute does NOT trigger use of the default
       *
       * @param string $attr    Attribute to access
       * @param string $default Default value to use if the attribute is not set
       *
       * @return string
       */
      public function getAttribute($attr, $default='') {
         return $this->hasAttribute($attr) ? parent::getAttribute($attr) : $default;
      }

      /**
       * Wrapper to DomElement->getAttributeNS with default value option
       *
       * Note: A set but emptry attribute does NOT trigger use of the default
       *
       * @param string $ns      Namespace of attribute
       * @param string $attr    Attribute to access
       * @param string $default Default value to use if the attribute is not set
       *
       * @return string
       */
      public function getAttributeNS($ns, $attr, $default='') {
         return $this->hasAttributeNS($ns, $attr) ? parent::getAttributeNS($ns, $attr) : $default;
      }

      /**
       * Helper method to get children by name
       *
       * @param string $tagName tagname to search for
       *
       * @return DomNodeList
       */
      public function getChildrenByTagName($tagName) {
         return $this->query("*[local-name()='$tagName']");
      }

      /**
       * Helper method to get children by name and namespace
       *
       * @param string $ns      namespace nodes have to be in
       * @param string $tagName tagname to search for
       *
       * @return DomNodeList
       */
      public function getChildrenByTagNameNS($ns, $tagName) {
         return $this->query("*[local-name()='$tagName' and namespace-uri()='$ns']");
      }

      /**
       * Check if current node and given one are in the same document
       *
       * @param DomNode $node Node to compare with
       *
       * @return boolean true on match, false if they differ
       *
       */
      public function inSameDocument(DomNode $node) {
         return ($this->ownerDocument->isSameNode($node->ownerDocument));
      }

   } // fDOMElement

}