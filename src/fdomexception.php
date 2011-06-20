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
 * @package    fDOM
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */

namespace TheSeer\fDOM {

   /**
    * fDOMException
    *
    * @category  PHP
    * @package   TheSeer\fDOM
    * @author    Arne Blankerts <arne@blankerts.de>
    * @access    public
    *
    */
   class fDOMException extends \Exception {

      const LoadError          = 1;
      const ParseError         = 2;
      const SaveError          = 3;
      const QueryError         = 4;
      const RegistrationFailed = 5;
      const NoDOMXPath         = 6;
      const UnboundPrefix      = 7;
      const DeprecatedMethod   = 8;

      /**
       * List of libxml error objects
       *
       * @var array
       */
      private $errorList;

      /**
       * Error message
       *
       * @var string
       */
      private $fullMessage = null;

      /**
       * Constructor
       *
       * @param string  $message Exception message
       * @param integer $code    Exception code
       *
       * @return void
       */
      public function __construct($message, $code = 0, $chain = NULL) {
         $this->errorList = libxml_get_errors();
         libxml_clear_errors();
         parent :: __construct($message, $code, $chain);

         $this->fullMessage = 'fDOMException: '.$message."\n\nDetails as follows:\n";

         foreach ($this->errorList as $error) {
            // hack, skip "attempt to load external pseudo error"
            if ($error->code=='1543') continue;

            if (empty($error->file)) {
               $this->fullMessage .= '[XML-STRING] ';
            } else {
               $this->fullMessage .= '['.$error->file.'] ';
            }

            $this->fullMessage .= '[Line: '.$error->line.' - Column: '.$error->column.'] ';

            switch ($error->level) {
               case LIBXML_ERR_WARNING:
                  $this->fullMessage .= "Warning $error->code: ";
                  break;
               case LIBXML_ERR_ERROR:
                  $this->fullMessage .= "Error $error->code: ";
                  break;
               case LIBXML_ERR_FATAL:
                  $this->fullMessage .= "Fatal Error $error->code: ";
                  break;
            }

            $this->fullMessage .= str_replace("\n", '', $error->message)."\n";
         }
      }

      /**
       * Accessor to fullMessage
       *
       * @return string
       */
      public function getFullMessage() {
         return $this->fullMessage;
      }

      /**
       * Accessor to errorList objets
       *
       * @return array
       */
      public function getErrorList() {
         return $this->errorList;
      }

      /**
       * Forwarder to fExceptions handle error method with local fullmessage
       *
       * @return void
       */
      public function handleError() {
         $this->message = $this->fullMessage;
         parent::handleError();
      }

      /**
       * Magic method for string context
       *
       * @return string
       */
      public function __toString() {
         return $this->fullMessage;
      }

   } // fDOMException

}
