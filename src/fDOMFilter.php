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
 * @link      http://github.com/theseer/fDOMdocument
 *
 */

namespace TheSeer\fDOM {

   /**
    * fDOMFilter interface
    *
    * @category Core
    * @package  Core
    * @access   public
    * @author   Arne Blankerts <theseer@fcms.de>
    *
    */
   interface fDOMFilter {

      /**
       * xml namespace this filter is for
       *
       * @return string Namespace URI
       */
      public function getNamespace();

      /**
       * Factory Helper to get Instance for given tagname
       *
       * @param string $tagname Tagname
       *
       * @return fDOMFilter Instance of a class implementing fDOMFilter
       */
      public function getInstanceForTagName($tagname);

      /**
       * Factory Helper to get Instance for attribute handler for current tag
       *
       * @return fDOMFilter Instance of a class implementing fDOMFilter
       */
      public function getInstanceForAttributes();

   }

   // ===============================================================================================================

   /**
    * fDOMFilterProcessor class
    *
    * @category Core
    * @package  Core
    * @access   public
    * @author   Arne Blankerts <theseer@fcms.de>
    *
    */
   class fDOMFilterProcessor {

      private $dom;
      private $filterList = array();

      /**
       * Constructor
       *
       * @param fDOMDocument $fDOM Reference to fDOMDocument this processor will work on
       *
       * @return void
       */
      public function __construct(fDOMDocument $fDOM) {
         $this->dom = $fDOM;
      }

      /**
       * Add Filter Implementation to list
       *
       * @param fDOMFilter $filter Instance of an fDOMFilter class
       *
       * @return void
       */
      public function addFilter(fDOMFilter $filter) {
         $this->filterList[$filter->getNamespace()] = $filter;
      }

      /**
       * Enter description here...
       *
       * @param DOMNode $node (optional) DomNode reference to begin cleaning at
       *
       * @return boolean true on success, false if cleaning failed
       */
      public function cleanup(DOMNode $node=null) {
         if (empty($this->filterList)) {
            throw new fDOMException('No filter defined for cleanup', fException::ERROR);
         }
         return $this->cleanupNode(is_null($node) ? $this->dom->documentElement : $node);
      }


      /**
       * Clean Processor call
       *
       * @param DOMNode $node DomNode to process
       *
       * @return boolean
       */
      private function cleanupNode(DOMNode $node) {

         if ($node->nodeType == XML_COMMENT_NODE ) {
            return false;
         }

         // get ns from node
         $xmlns = $node->namespaceURI;
         if (is_null($xmlns)) {
            $xmlns = 'none';
         }

         // filter class for namespace?
         if (isset($this->filterList[$xmlns])) {
            $filterCore = $this->filterList[$xmlns];
            $filter     = $filterCore->getInstanceForTagName($node->localName);

            if ($node->hasAttributes()) {
               $this->processAttributes($node, $filter);
            }

            if ($node->hasChildNodes()) {
               $this->processChildNodes($node, $filter);
            }

            return true;

         } else {

            // no filter class for this xmlns, remove node if possible
            if ($node->parentNode instanceof DOMNode) {
               $node->parentNode->removeChild($node);
               return true;
            } else {
               // cannot remove this node here, thus return false
               return false;
            }
         }
      } // cleanupNode

      /**
       * Helper to loop over attributes
       *
       * @param fDOMNode   $node   Domnode to process attributes of
       * @param fDOMFilter $filter Instance of filter
       *
       * @return void
       */
      private function processAttributes(fDOMNode $node, fDOMFilter $filter) {
         // loop attributes
         foreach($node->attributes as $attr) {

            if ($attr->namespaceURI!='' && $attr->namespaceURI!=$node->namespaceURI) {

               // ignore xml attributes
               if ($attr->namespaceURI=='http://www.w3.org/XML/1998/namespace') {
                  continue;
               }

               // custom namespace handling needed
               $custom = $this->filterList[$xmlns]->getInstanceForAttributes();

               if (!$custom || !in_array($attr->name, $custom->attributes)) {
                  $node->removeAttributeNode($attr);
                  continue;
               }

               // custom function defined? If so, use
               if (method_exists($custom, 'attribute_'.$attr->name)) {
                  $node->setAttribute($attr->name, $custom->{'attribute_'.$attr->name}($attr->value));
               }

            } else {
               // same namespace as node

               if (!in_array($attr->name, $filter->attributes)) {
                  $node->removeAttributeNode($attr);
                  continue;
               }

               // filter function defined? If so, use
               if (method_exists($filter, 'attribute_'.$attr->name)) {
                  $node->setAttribute($attr->name, $filter->{'attribute_'.$attr->name}($attr->value));
               }
            }

         } // attributes
      }

      /**
       * Helper to loop over childnodes of given node
       *
       * @param fDOMNode   $node   Node to process children of
       * @param fDOMFilter $filter Instance of current fDOMFilter
       *
       * @return void
       */
      private function processChildNodes(fDOMNode $node, fDOMFilter $filter) {

         // remove childnodes if empty is enforced
         if ($filter->isEmpty) {
            foreach($node->childNodes as $c) {
               $node->removeChild($c);
            }
            return;
         }

         // loop children
         foreach($node->childNodes as $child) {

            if ($child instanceof DOMText) {
               if (!$filter->textContent && !$child->isWhitespaceInElementContent() ) {
                  $node->removeChild($child);
               }
               continue;
            }

            // remove comments and PIs ;)
            if ($child->nodeType == XML_COMMENT_NODE || $child->nodeType == XML_PI_NODE) {
               $node->removeChild($child);
               continue;
            }

            // still same xmlns and child is a DOMNode
            if ($child instanceof DOMNode && $child->namespaceURI==$xmlns) {

               // node not allowed? -> remove
               if (!in_array($child->localName, $filter->children)) {
                  $node->removeChild($child);
                  continue;
               }

            }

            // process child itself - recurse on $child
            $rc = $this->cleanupNode($child);
            if (!$rc) {
               $node->removeChild($child);
            }

         }

      }
   }

   // ===============================================================================================================

   /**
    * fDOMFilterNode class
    *
    * This class provides some general helper functions for actual filter classes
    *
    * @category Core
    * @package  Core
    * @access   public
    * @author   Arne Blankerts <theseer@fcms.de>
    *
    */
   abstract class fDOMFilterNode {

      public $attributes  = array();
      public $children    = array();
      public $textContent = false;
      public $isEmpty     = false;

      /**
       * regex validator helper
       *
       * @param string $pattern  regular expression to use
       * @param string $value    value to use regex on
       * @param string $fallback default value in case regex failed
       *
       * @return string
       */
      public function validateRegex($pattern, $value, $fallback) {
         return preg_match($pattern, $value) ? $value : $fallback;
      }

      /**
       * only http/https or local uri allowed
       *
       * @param string $val URI String
       *
       * @return string Original URI if it was valid, otherwise empty string
       */
      public function uriValidator($val) {
         return preg_match('=^((http|https):.*|[^:]*)$=', $val) ? $val : '';
      }

      /**
       * textContent validator
       *
       * This is a stub function
       *
       * @return boolean true
       */
      public function validateContent() {
         return true;
      }

   }

}