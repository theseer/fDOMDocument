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
 * @package    fDOM
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */

namespace TheSeer\fDOM {

    /**
     * fDOMFilterXHTML
     *
     *
     *  Filter a given xhtml dom document for security based on
     *    - whitelist, based on xhtml 1.0 tans dtd with the following removed
     *       - script, style, frame/iframe, font
     *       - on*-Events
     *       - meta, link, base
     *       - applet, object (embed is not xhtml compliant)
     *
     *    - removal of nonn matching tags/attributes and their respective children
     *
     *
     * @category  PHP
     * @package   TheSeer\fDOM
     * @author    Arne Blankerts <arne@blankerts.de>
     * @access    public
     *
     */
    class fDOMFilterXHTML implements fDOMFilter {

        public function getNamespace() {
            return 'http://www.w3.org/1999/xhtml';
        }

        // ***** Attriubtes

        //<!-- core attributes common to most elements
        //  id       document-wide unique id
        //  class    space separated list of classes
        //  style    associated style info
        //  title    advisory title/amplification
        //-->
        static $coreattrs = array('id','class','title');

        //<!-- internationalization attributes
        //  lang        language code (backwards compatible)
        //  xml:lang    language code (as per XML 1.0 spec)
        //  dir         direction for weak/neutral text
        //-->
        static $i18n = array('lang','xml:lang','dir');

        //<!-- attributes for elements that can get the focus
        //  accesskey   accessibility key character
        //  tabindex    position in tabbing order
        //  onfocus     the element got the focus
        //  onblur      the element lost the focus
        //-->
        static $focus = array('accesskey','tabindex');

        // group of attributes
        static $attrs = array();


        // ***** Elements

        //<!ENTITY % fontstyle.extra "big | small | font | basefont">
        //<!ENTITY % fontstyle.basic "tt | i | b | u | s | strike ">
        //<!ENTITY % fontstyle "%fontstyle.basic; | %fontstyle.extra;">
        static $fontstyle = array('big','small','tt','b','i','u','s','strike');

        //<!ENTITY % special.extra "object | applet | img | map | iframe">
        //<!ENTITY % special.basic "br | span | bdo">
        //<!ENTITY % special "%special.basic; | %special.extra;">
        static $special = array('img','map','br','span','bdo');

        //<!ENTITY % phrase.extra "sub | sup">
        //<!ENTITY % phrase.basic "em | strong | dfn | code | q | samp | kbd | var | cite | abbr | acronym">
        //<!ENTITY % phrase "%phrase.basic; | %phrase.extra;">
        static $phrase = array('sub','sup','em','strong','dfn','code','q','samp','kdb','var','cite','abbr','acronym');

        //<!ENTITY % heading "h1|h2|h3|h4|h5|h6">
        static $heading = array('h1','h2','h3','h4','h5','h6');

        //<!ENTITY % lists "ul | ol | dl | menu | dir">
        static $lists = array('ul','ol','dl','menu','dir');

        //<!ENTITY % blocktext "pre | hr | blockquote | address | center | noframes">
        static $blocktext = array('pre','hr','blockquote','address','center','noframes');

        // <!ENTITY % inline.forms "input | select | textarea | label | button">
        static $form = array('input','select', 'textarea', 'label','button');

        // <!ENTITY % misc.inline "ins | del | script">
        // <!ENTITY % misc "noscript | %misc.inline;">
        static $misc = array('noscript','ins','del');

        // Groups of elements
        static $inline;
        static $block;
        static $flow;

        // private variables
        private $matrix = array();

        public function __construct() {

            // <!ENTITY % attrs "%coreattrs; %i18n; %events;">
            self::$attrs = array_merge(self::$coreattrs, self::$i18n);

            // <!ENTITY % inline "a | %special; | %fontstyle; | %phrase; | %inline.forms;">
            self::$inline = array_merge( array('a'), self::$special, self::$fontstyle, self::$phrase );

            // <!ENTITY % block "p | %heading; | div | %lists; | %blocktext; | isindex | fieldset | table">
            self::$block  = array_merge( self::$heading, self::$lists, self::$blocktext,
            array('p','div','fieldset','table' ) );

            //<!ENTITY % Flow "(#PCDATA | %block; | form | %inline; | %misc;)*">
            self::$flow =  array_merge( self::$block, self::$inline, self::$misc, array('form'));
        }

        public function getInstanceForTagName($tagname) {

            $classname = 'fDOMFilterXHTML_'.$tagname;
            if (!class_exists($classname)) {
                return false;
            }

            if (!isset($this->matrix[$tagname])) {
                $this->matrix[$tagname] = new $classname;
            }

            return $this->matrix[$tagname];

        }

        public function getInstanceForAttributes() {
            // there are no "standalone attributes"
            return false;
        }


    } // fFilterXHTML


    /*
     * XHTML Node Classes
     */

    //<!ELEMENT html (head, body)>
    //<!ATTLIST html
    //  %i18n;
    //  id          ID             #IMPLIED
    //  xmlns       %URI;          #FIXED 'http://www.w3.org/1999/xhtml'
    //  >
    class fDOMFilterXHTML_html extends fDOMFilterNode {

        public $children = array('head','body');

        public function __construct() {
            $this->attributes = array_merge( fDOMFilterXHTML::$i18n, array('id','xmlns'));
        }
    }

    //<!ENTITY % head.misc "(script|style|meta|link|object|isindex)*">
    //<!ELEMENT head (%head.misc;,
    //     ((title, %head.misc;, (base, %head.misc;)?) |
    //      (base, %head.misc;, (title, %head.misc;))))>
    //<!ATTLIST head
    //  %i18n;
    //  id          ID             #IMPLIED
    //  profile     %URI;          #IMPLIED
    //  >
    class fDOMFilterXHTML_head extends fDOMFilterNode {

        public $children = array('title');

        public function __construct() {
            $this->attributes = array_merge( fDOMFilterXHTML::$i18n, array('id','profile'));
        }
    }

    //<!ELEMENT title (#PCDATA)>
    //<!ATTLIST title
    //  %i18n;
    //  id          ID             #IMPLIED
    //  >
    class fDOMFilterXHTML_title extends fDOMFilterNode {

        public $textContent = true;

        public function __construct() {
            $this->attributes = array_merge( fDOMFilterXHTML::$i18n, array('id'));
        }
    }

    //<!ELEMENT noscript %Flow;>
    //<!ATTLIST noscript
    //  %attrs;
    //  >
    class fDOMFilterXHTML_noscript extends fDOMFilterNode {

        public $textContent = true;

        public function __construct() {
            $this->attributes = fDOMFilterXHTML::$attrs;
            $this->children = fDOMFilterXHTML::$flow;
        }
    }

    //<!ELEMENT body %Flow;>
    //<!ATTLIST body
    //  %attrs;
    //  onload      %Script;       #IMPLIED
    //  onunload    %Script;       #IMPLIED
    //  background  %URI;          #IMPLIED
    //  bgcolor     %Color;        #IMPLIED
    //  text        %Color;        #IMPLIED
    //  link        %Color;        #IMPLIED
    //  vlink       %Color;        #IMPLIED
    //  alink       %Color;        #IMPLIED
    //  >
    class fDOMFilterXHTML_body extends fDOMFilterNode {

        public $textContent = true;

        public function __construct() {
            $this->attributes = array_merge(fDOMFilterXHTML::$attrs,
            array('bgcolor','text','link','vlink','alink'));
            $this->children = fDOMFilterXHTML::$flow;
        }
    }

    //<!ELEMENT div %Flow;>  <!-- generic language/style container -->
    //<!ATTLIST div
    //  %attrs;
    //  %TextAlign;
    //  >
    class fDOMFilterXHTML_div extends fDOMFilterNode {

        public $textContent = true;

        public function __construct() {
            $this->attributes = array_merge(fDOMFilterXHTML::$attrs, array('align'));
            $this->children = fDOMFilterXHTML::$flow;
        }

        public function attr_align($value) {
            return in_array($value, array('left', 'center', 'right', 'justify')) ? $value : 'left';
        }
    }

    //<!ELEMENT p %Inline;>
    //<!ATTLIST p
    //  %attrs;
    //  %TextAlign;
    //  >
    class fDOMFilterXHTML_p extends fDOMFilterXHTML_div {

        public $textContent = true;

        public function __construct() {
            $this->attributes = array_merge(fDOMFilterXHTML::$attrs, array('align'));
            $this->children = fDOMFilterXHTML::$inline;
        }
    }

    //<!ELEMENT h1  %Inline;>
    //<!ATTLIST h1
    //  %attrs;
    //  %TextAlign;
    //  >
    class fDOMFilterXHTML_h1 extends fDOMFilterXHTML_p {}

    //<!ELEMENT h2 %Inline;>
    //<!ATTLIST h2
    //  %attrs;
    //  %TextAlign;
    //  >
    class fDOMFilterXHTML_h2 extends fDOMFilterXHTML_p {}

    //<!ELEMENT h3 %Inline;>
    //<!ATTLIST h3
    //  %attrs;
    //  %TextAlign;
    //  >
    class fDOMFilterXHTML_h3 extends fDOMFilterXHTML_p {}

    //<!ELEMENT h4 %Inline;>
    //<!ATTLIST h4
    //  %attrs;
    //  %TextAlign;
    //  >
    class fDOMFilterXHTML_h4 extends fDOMFilterXHTML_p {}

    //<!ELEMENT h5 %Inline;>
    //<!ATTLIST h5
    //  %attrs;
    //  %TextAlign;
    //  >
    class fDOMFilterXHTML_h5 extends fDOMFilterXHTML_p {}

    //<!ELEMENT h6 %Inline;>
    //<!ATTLIST h6
    //  %attrs;
    //  %TextAlign;
    //  >
    class fDOMFilterXHTML_h6 extends fDOMFilterXHTML_p {}

    //<!ELEMENT ul (li)+>
    //<!ATTLIST ul
    //  %attrs;
    //  type        %ULStyle;     #IMPLIED
    //  compact     (compact)     #IMPLIED
    //  >
    class fDOMFilterXHTML_ul extends fDOMFilterNode {

        public $children = array('li');

        public function __construct() {
            $this->attributes = array_merge(fDOMFilterXHTML::$attrs, array('type', 'compact'));
        }

        public function attr_type($value) {
            //<!ENTITY % ULStyle "(disc|square|circle)">
            return in_array($value, array('disc', 'square', 'circle')) ? $value : 'disc';
        }

        public function attr_compact($value) {
            return 'compact';
        }
    }

    //<!ENTITY % OLStyle "CDATA">
    //<!ELEMENT ol (li)+>
    //<!ATTLIST ol
    //  %attrs;
    //  type        %OLStyle;      #IMPLIED
    //  compact     (compact)      #IMPLIED
    //  start       %Number;       #IMPLIED
    //  >
    class fDOMFilterXHTML_ol extends fDOMFilterNode {

        public $children = array('li');
        public $attributes;

        public function __construct() {
            $this->attributes = array_merge(fDOMFilterXHTML::$attrs, array('type','compact','start'));
        }

        public function attr_compact($value) {
            return 'compact';
        }

        public function attr_start($value) {
            return (string)(int)$value;
        }
    }

    //<!ELEMENT menu (li)+>
    //<!ATTLIST menu
    //  %attrs;
    //  compact     (compact)     #IMPLIED
    //  >
    class fDOMFilterXHTML_menu extends fDOMFilterNode {

        public $children = array('li');

        public function __construct() {
            $this->attributes = array_merge(fDOMFilterXHTML::$attrs, array('type','compact'));
        }

        public function attr_compact($value) {
            return 'compact';
        }

    }

    //<!ELEMENT dir (li)+>
    //<!ATTLIST dir
    //  %attrs;
    //  compact     (compact)     #IMPLIED
    //  >
    class fDOMFilterXHTML_dir extends fDOMFilterXHTML_menu {};

    //<!ELEMENT li %Flow;>
    //<!ATTLIST li
    //  %attrs;
    //  type        %LIStyle;      #IMPLIED
    //  value       %Number;       #IMPLIED
    //  >
    class fDOMFilterXHTML_li extends fDOMFilterNode {

        public $textContent = true;

        public function __construct() {
            $this->children = fDOMFilterXHTML::$flow;
            $this->attributes = array_merge(fDOMFilterXHTML::$attrs, array('type','value'));
        }
    }

    //<!ELEMENT dl (dt|dd)+>
    //<!ATTLIST dl
    //  %attrs;
    //  compact     (compact)      #IMPLIED
    //  >
    class fDOMFilterXHTML_dl extends fDOMFilterNode {

        public $children = array('dt','dd');

        public function __construct() {
            $this->attributes = array_merge(fDOMFilterXHTML::$attrs, array('compat'));
        }

        public function attr_compact($value) {
            return 'compact';
        }
    }

    //<!ELEMENT dt %Inline;>
    //<!ATTLIST dt
    //  %attrs;
    //  >
    class fDOMFilterXHTML_dt extends fDOMFilterNode {

        public $textContent = true;

        public function __construct() {
            $this->children = fDOMFilterXHTML::$inline;
            $this->attributes = fDOMFilterXHTML::$attrs;
        }
    }

    //<!ELEMENT dd %Flow;>
    //<!ATTLIST dd
    //  %attrs;
    //  >
    class fDOMFilterXHTML_dd extends fDOMFilterNode {

        public $textContent = true;

        public function __construct() {
            $this->children = fDOMFilterXHTML::$flow;
            $this->attributes = fDOMFilterXHTML::$attrs;
        }
    }

    //<!ENTITY % misc.inline "ins | del | script">
    //<!ELEMENT address (#PCDATA | %inline; | %misc.inline; | p)*>
    //<!ATTLIST address
    //  %attrs;
    //  >
    class fDOMFilterXHTML_address extends fDOMFilterNode {

        public $textContent = true;

        public function __construct() {
            $this->children = array_merge( fDOMFilterXHTML::$inline, array('ins','del','strike','p'));
            $this->attributes = fDOMFilterXHTML::$attrs;
        }
    }

    //<!ELEMENT hr EMPTY>
    //<!ATTLIST hr
    //  %attrs;
    //  align       (left|center|right) #IMPLIED
    //  noshade     (noshade)      #IMPLIED
    //  size        %Pixels;       #IMPLIED
    //  width       %Length;       #IMPLIED
    //  >
    class fDOMFilterXHTML_hr extends fDOMFilterNode {

        public $isEmpty = true;

        public function __construct() {
            $this->attributes = array_merge(fDOMFilterXHTML::$attrs, array('align','noshade','size','width'));
        }

        public function attr_noshade($value) {
            return 'noshade';
        }
    }

    //<!ENTITY % pre.content
    //   "(#PCDATA | a | %special.basic; | %fontstyle.basic; | %phrase.basic; |
    //	   %inline.forms; | %misc.inline;)*">
    //<!ELEMENT pre %pre.content;>
    //<!ATTLIST pre
    //  %attrs;
    //  width       %Number;      #IMPLIED
    //  xml:space   (preserve)    #FIXED 'preserve'
    //  >
    class fDOMFilterXHTML_pre extends fDOMFilterNode {

        public $textContent = true;

        public function __construct() {
            $this->children = fDOMFilterXHTML::$inline;
            $this->attributes = array_merge(fDOMFilterXHTML::$attrs, array('xml:space','width'));

            // pre uses %Inline excluding img, big, small, font, or basefont
            foreach( array('img', 'big', 'small', 'font', 'basefont') as $x) {
                unset($this->children[array_search($x, $this->children)]);
            }
        }
    }

    //<!ELEMENT blockquote %Flow;>
    //<!ATTLIST blockquote
    //  %attrs;
    //  cite        %URI;          #IMPLIED
    //  >
    class fDOMFilterXHTML_blockquote extends fDOMFilterNode {

        public $textContent = true;

        public function __construct() {
            $this->children = fDOMFilterXHTML::$flow;
            $this->attributes = array_merge(fDOMFilterXHTML::$attrs, array('cite'));
        }
    }

    //<!ELEMENT center %Flow;>
    //<!ATTLIST center
    //  %attrs;
    //  >
    class fDOMFilterXHTML_center extends fDOMFilterXHTML_dd {}

    //<!ELEMENT ins %Flow;>
    //<!ATTLIST ins
    //  %attrs;
    //  cite        %URI;          #IMPLIED
    //  datetime    %Datetime;     #IMPLIED
    //  >
    class fDOMFilterXHTML_ins extends fDOMFilterNode {

        public $textContent = true;

        public function __construct() {
            $this->children = fDOMFilterXHTML::$flow;
            $this->attributes = array_merge(fDOMFilterXHTML::$attrs, array('cite','datetime'));
        }
    }

    //<!ELEMENT del %Flow;>
    //<!ATTLIST del
    //  %attrs;
    //  cite        %URI;          #IMPLIED
    //  datetime    %Datetime;     #IMPLIED
    //  >
    class fDOMFilterXHTML_del extends fDOMFilterXHTML_ins {}

    //<!ELEMENT a %a.content;>
    //<!ATTLIST a
    //  %attrs;
    //  %focus;
    //  charset     %Charset;      #IMPLIED
    //  type        %ContentType;  #IMPLIED
    //  name        NMTOKEN        #IMPLIED
    //  href        %URI;          #IMPLIED
    //  hreflang    %LanguageCode; #IMPLIED
    //  rel         %LinkTypes;    #IMPLIED
    //  rev         %LinkTypes;    #IMPLIED
    //  shape       %Shape;        "rect"
    //  coords      %Coords;       #IMPLIED
    //  target      %FrameTarget;  #IMPLIED
    //  >
    class fDOMFilterXHTML_a extends fDOMFilterNode {

        public $textContent = true;

        public function __construct() {

            // $a.content == %inline, without 'a'
            $this->children = fDOMFilterXHTML::$inline;
            unset($this->children[array_search('a', $this->children)]);

            $this->attributes = array_merge(fDOMFilterXHTML::$attrs, fDOMFilterXHTML::$focus,
            array('charset','type','name','href','hreflang','rel','rev',
                                             'shape','coords','target'));
        }
    }

    //<!ELEMENT span %Inline;> <!-- generic language/style container -->
    //<!ATTLIST span
    //  %attrs;
    //  >
    class fDOMFilterXHTML_span extends fDOMFilterXHTML_dt {}

    //<!ELEMENT bdo %Inline;>  <!-- I18N BiDi over-ride -->
    //<!ATTLIST bdo
    //  %coreattrs;
    //  %events;
    //  lang        %LanguageCode; #IMPLIED
    //  xml:lang    %LanguageCode; #IMPLIED
    //  dir         (ltr|rtl)      #REQUIRED
    //  >
    class fDOMFilterXHTML_bdo extends fDOMFilterNode {

        public $textContent = true;

        public function __construct() {
            $this->children = fDOMFilterXHTML::$inline;
            $this->attributes = array_merge(fDOMFilterXHTML::$coreattrs, array('lang','xml:lang','dir'));
        }

        public function attribute_dir($value) {
            return in_array($value, array('ltr','rtl')) ? $value : 'ltr';
        }
    }

    //
    //<!ELEMENT br EMPTY>   <!-- forced line break -->
    //<!ATTLIST br
    //  %coreattrs;
    //  clear       (left|all|right|none) "none"
    //  >
    class fDOMFilterXHTML_br extends fDOMFilterNode {
        public $isEmpty = true;

        public function __construct() {
            $this->attributes = array_merge(fDOMFilterXHTML::$coreattrs, array('clear'));
        }

        public function attribute_clear($value) {
            return in_array($value, array('left', 'all', 'right' ,'none')) ? $value : 'none';
        }
    }

    //<!ELEMENT em %Inline;>   <!-- emphasis -->
    //<!ATTLIST em %attrs;>
    class fDOMFilterXHTML_em extends fDOMFilterXHTML_dt {}

    //<!ELEMENT strong %Inline;>   <!-- strong emphasis -->
    //<!ATTLIST strong %attrs;>
    class fDOMFilterXHTML_strong extends fDOMFilterXHTML_dt {}

    //<!ELEMENT dfn %Inline;>   <!-- definitional -->
    //<!ATTLIST dfn %attrs;>
    class fDOMFilterXHTML_dfn extends fDOMFilterXHTML_dt {}

    //<!ELEMENT code %Inline;>   <!-- program code -->
    //<!ATTLIST code %attrs;>
    class fDOMFilterXHTML_code extends fDOMFilterXHTML_dt {}

    //<!ELEMENT samp %Inline;>   <!-- sample -->
    //<!ATTLIST samp %attrs;>
    class fDOMFilterXHTML_samp extends fDOMFilterXHTML_dt {}

    //<!ELEMENT kbd %Inline;>  <!-- something user would type -->
    //<!ATTLIST kbd %attrs;>
    class fDOMFilterXHTML_kbd extends fDOMFilterXHTML_dt {}

    //<!ELEMENT var %Inline;>   <!-- variable -->
    //<!ATTLIST var %attrs;>
    class fDOMFilterXHTML_var extends fDOMFilterXHTML_dt {}

    //<!ELEMENT cite %Inline;>   <!-- citation -->
    //<!ATTLIST cite %attrs;>
    class fDOMFilterXHTML_cite extends fDOMFilterXHTML_dt {}

    //<!ELEMENT abbr %Inline;>   <!-- abbreviation -->
    //<!ATTLIST abbr %attrs;>
    class fDOMFilterXHTML_abbr extends fDOMFilterXHTML_dt {}

    //<!ELEMENT acronym %Inline;>   <!-- acronym -->
    //<!ATTLIST acronym %attrs;>
    class fDOMFilterXHTML_acronym extends fDOMFilterXHTML_dt {}

    //<!ELEMENT q %Inline;>   <!-- inlined quote -->
    //<!ATTLIST q
    //  %attrs;
    //  cite        %URI;          #IMPLIED
    //  >

    //<!ELEMENT sub %Inline;> <!-- subscript -->
    //<!ATTLIST sub %attrs;>
    class fDOMFilterXHTML_sub extends fDOMFilterXHTML_dt {}

    //<!ELEMENT sup %Inline;> <!-- superscript -->
    //<!ATTLIST sup %attrs;>
    class fDOMFilterXHTML_sup extends fDOMFilterXHTML_dt {}

    //<!ELEMENT tt %Inline;>   <!-- fixed pitch font -->
    //<!ATTLIST tt %attrs;>
    class fDOMFilterXHTML_tt extends fDOMFilterXHTML_dt {}

    //<!ELEMENT i %Inline;>   <!-- italic font -->
    //<!ATTLIST i %attrs;>
    class fDOMFilterXHTML_i extends fDOMFilterXHTML_dt {}

    //<!ELEMENT b %Inline;>   <!-- bold font -->
    //<!ATTLIST b %attrs;>
    class fDOMFilterXHTML_b extends fDOMFilterXHTML_dt {}

    //<!ELEMENT big %Inline;>   <!-- bigger font -->
    //<!ATTLIST big %attrs;>
    class fDOMFilterXHTML_big extends fDOMFilterXHTML_dt {}

    //<!ELEMENT small %Inline;>   <!-- smaller font -->
    //<!ATTLIST small %attrs;>
    class fDOMFilterXHTML_small extends fDOMFilterXHTML_dt {}

    //<!ELEMENT u %Inline;>   <!-- underline -->
    //<!ATTLIST u %attrs;>
    class fDOMFilterXHTML_u extends fDOMFilterXHTML_dt {}

    //<!ELEMENT s %Inline;>   <!-- strike-through -->
    //<!ATTLIST s %attrs;>
    class fDOMFilterXHTML_s extends fDOMFilterXHTML_dt {}

    //<!ELEMENT strike %Inline;>   <!-- strike-through -->
    //<!ATTLIST strike %attrs;>
    class fDOMFilterXHTML_strike extends fDOMFilterXHTML_dt {}

    //<!ELEMENT basefont EMPTY>  <!-- base font size -->
    //<!ATTLIST basefont
    //  id          ID             #IMPLIED
    //  size        CDATA          #REQUIRED
    //  color       %Color;        #IMPLIED
    //  face        CDATA          #IMPLIED
    //  >
    //
    //<!ELEMENT font %Inline;> <!-- local change to font -->
    //<!ATTLIST font
    //  %coreattrs;
    //  %i18n;
    //  size        CDATA          #IMPLIED
    //  color       %Color;        #IMPLIED
    //  face        CDATA          #IMPLIED
    //  >


    //<!--=================== Images ===========================================-->
    //
    //<!--
    //   To avoid accessibility problems for people who aren't
    //   able to see the image, you should provide a text
    //   description using the alt and longdesc attributes.
    //   In addition, avoid the use of server-side image maps.
    //-->
    //
    //<!ELEMENT img EMPTY>
    //<!ATTLIST img
    //  %attrs;
    //  src         %URI;          #REQUIRED
    //  alt         %Text;         #REQUIRED
    //  name        NMTOKEN        #IMPLIED
    //  longdesc    %URI;          #IMPLIED
    //  height      %Length;       #IMPLIED
    //  width       %Length;       #IMPLIED
    //  usemap      %URI;          #IMPLIED
    //  ismap       (ismap)        #IMPLIED
    //  align       %ImgAlign;     #IMPLIED
    //  border      %Length;       #IMPLIED
    //  hspace      %Pixels;       #IMPLIED
    //  vspace      %Pixels;       #IMPLIED
    //  >
    class fDOMFilterXHTML_img extends fDOMFilterNode {
        public $isEmpty = true;

        public function __construct() {
            $this->attributes = array_merge(fDOMFilterXHTML::$attrs,
            array('src','alt','name','longdesc','height','width',
                                             'usemap','ismap','align','border','hspace','vspace'));
        }

        public function attribute_src($value) {
            return $this->uriValidator($value);
        }

    }

    //<!ELEMENT map ((%block; | form | %misc;)+ | area+)>
    //<!ATTLIST map
    //  %i18n;
    //  %events;
    //  id          ID             #REQUIRED
    //  class       CDATA          #IMPLIED
    //  style       %StyleSheet;   #IMPLIED
    //  title       %Text;         #IMPLIED
    //  name        CDATA          #IMPLIED
    //  >
    class fDOMFilterXHTML_map extends fDOMFilterNode {

        public function __construct() {
            $this->attributes = array_merge(fDOMFilterXHTML::$i18n, array('id','class','title','name'));
            $this->chldren = array_merge(fDOMFilterXHTML::$block, fDOMFilterXHTML::$misc,
            array('form','area'));
        }
    }

    //<!ELEMENT area EMPTY>
    //<!ATTLIST area
    //  %attrs;
    //  %focus;
    //  shape       %Shape;        "rect"
    //  coords      %Coords;       #IMPLIED
    //  href        %URI;          #IMPLIED
    //  nohref      (nohref)       #IMPLIED
    //  alt         %Text;         #REQUIRED
    //  target      %FrameTarget;  #IMPLIED
    //  >
    class fDOMFilterXHTML_area extends fDOMFilterNode {
        public $isEmpty = true;

        public function __construct() {
            $this->attributes = array_merge(fDOMFilterXHTML::$attrs, fDOMFilterXHTML::$focus,
            array('shape','coords','href','nohref','alt','target'));
        }
    }

    //<!ELEMENT form %form.content;>   <!-- forms shouldn't be nested -->
    //<!ATTLIST form
    //  %attrs;
    //  action      %URI;          #REQUIRED
    //  method      (get|post)     "get"
    //  name        NMTOKEN        #IMPLIED
    //  enctype     %ContentType;  "application/x-www-form-urlencoded"
    //  onsubmit    %Script;       #IMPLIED
    //  onreset     %Script;       #IMPLIED
    //  accept      %ContentTypes; #IMPLIED
    //  accept-charset %Charsets;  #IMPLIED
    //  target      %FrameTarget;  #IMPLIED
    //  >
    class fDOMFilterXHTML_form extends fDOMFilterNode {

        public $textContent = true;

        public function __construct() {

            // $form.content == %inline, without 'form'
            $this->children = fDOMFilterXHTML::$inline;
            unset($this->children[array_search('a', $this->children)]);

            $this->attributes = array_merge(fDOMFilterXHTML::$attrs,
            array('action','method','name','enctype','accept','accpet-charset','target'));
        }
    }

    //<!ELEMENT label %Inline;>
    //<!ATTLIST label
    //  %attrs;
    //  for         IDREF          #IMPLIED
    //  accesskey   %Character;    #IMPLIED
    //  onfocus     %Script;       #IMPLIED
    //  onblur      %Script;       #IMPLIED
    //  >
    class fDOMFilterXHTML_label extends fDOMFilterNode {

        public $textContent = true;

        public function __construct() {

            // $form.content == %inline, without 'form'
            $this->children = fDOMFilterXHTML::$inline;

            $this->attributes = array_merge(fDOMFilterXHTML::$attrs, array('for','accesskey'));
        }
    }

    //<!ELEMENT input EMPTY>     <!-- form control -->
    //<!ATTLIST input
    //  %attrs;
    //  %focus;
    //  type        %InputType;    "text"
    //  name        CDATA          #IMPLIED
    //  value       CDATA          #IMPLIED
    //  checked     (checked)      #IMPLIED
    //  disabled    (disabled)     #IMPLIED
    //  readonly    (readonly)     #IMPLIED
    //  size        CDATA          #IMPLIED
    //  maxlength   %Number;       #IMPLIED
    //  src         %URI;          #IMPLIED
    //  alt         CDATA          #IMPLIED
    //  usemap      %URI;          #IMPLIED
    //  onselect    %Script;       #IMPLIED
    //  onchange    %Script;       #IMPLIED
    //  accept      %ContentTypes; #IMPLIED
    //  align       %ImgAlign;     #IMPLIED
    //  >
    class fDOMFilterXHTML_input extends fDOMFilterNode {

        public $isEmpty = true;

        public function __construct() {

            $this->attributes = array_merge(fDOMFilterXHTML::$attrs, fDOMFilterXHTML::$focus,
            array('type','name','checked','disabled','readonly','size','maxlength',
                                                                'src','alt','usemap','accept','align'));
        }

        //<!ENTITY % InputType
        //  "(text | password | checkbox |
        //    radio | submit | reset |
        //    file | hidden | image | button)"
        //   >
        public function attribute_type($value) {
            return  in_array($value, array('text','password','checkbox','radio','submit','reset','file','hidden','image','button'))?$value:'text';
        }

        public function attribute_src($value) {
            return $this->uriValidator($value);
        }

    }

    //<!ELEMENT select (optgroup|option)+>  <!-- option selector -->
    //<!ATTLIST select
    //  %attrs;
    //  name        CDATA          #IMPLIED
    //  size        %Number;       #IMPLIED
    //  multiple    (multiple)     #IMPLIED
    //  disabled    (disabled)     #IMPLIED
    //  tabindex    %Number;       #IMPLIED
    //  onfocus     %Script;       #IMPLIED
    //  onblur      %Script;       #IMPLIED
    //  onchange    %Script;       #IMPLIED
    //  >
    //
    //<!ELEMENT optgroup (option)+>   <!-- option group -->
    //<!ATTLIST optgroup
    //  %attrs;
    //  disabled    (disabled)     #IMPLIED
    //  label       %Text;         #REQUIRED
    //  >
    //
    //<!ELEMENT option (#PCDATA)>     <!-- selectable choice -->
    //<!ATTLIST option
    //  %attrs;
    //  selected    (selected)     #IMPLIED
    //  disabled    (disabled)     #IMPLIED
    //  label       %Text;         #IMPLIED
    //  value       CDATA          #IMPLIED
    //  >
    //
    //<!ELEMENT textarea (#PCDATA)>     <!-- multi-line text field -->
    //<!ATTLIST textarea
    //  %attrs;
    //  %focus;
    //  name        CDATA          #IMPLIED
    //  rows        %Number;       #REQUIRED
    //  cols        %Number;       #REQUIRED
    //  disabled    (disabled)     #IMPLIED
    //  readonly    (readonly)     #IMPLIED
    //  onselect    %Script;       #IMPLIED
    //  onchange    %Script;       #IMPLIED
    //  >
    //
    //<!--
    //  The fieldset element is used to group form fields.
    //  Only one legend element should occur in the content
    //  and if present should only be preceded by whitespace.
    //-->
    //<!ELEMENT fieldset (#PCDATA | legend | %block; | form | %inline; | %misc;)*>
    //<!ATTLIST fieldset
    //  %attrs;
    //  >
    //
    //<!ENTITY % LAlign "(top|bottom|left|right)">
    //
    //<!ELEMENT legend %Inline;>     <!-- fieldset label -->
    //<!ATTLIST legend
    //  %attrs;
    //  accesskey   %Character;    #IMPLIED
    //  align       %LAlign;       #IMPLIED
    //  >
    //
    //<!--
    // Content is %Flow; excluding a, form, form controls, iframe
    //-->
    //<!ELEMENT button %button.content;>  <!-- push button -->
    //<!ATTLIST button
    //  %attrs;
    //  %focus;
    //  name        CDATA          #IMPLIED
    //  value       CDATA          #IMPLIED
    //  type        (button|submit|reset) "submit"
    //  disabled    (disabled)     #IMPLIED
    //  >
    //
    //<!-- single-line text input control (DEPRECATED) -->
    //<!ELEMENT isindex EMPTY>
    //<!ATTLIST isindex
    //  %coreattrs;
    //  %i18n;
    //  prompt      %Text;         #IMPLIED
    //  >
    //
    //<!--======================= Tables =======================================-->
    //
    //<!-- Derived from IETF HTML table standard, see [RFC1942] -->
    //
    //<!--
    // The border attribute sets the thickness of the frame around the
    // table. The default units are screen pixels.
    //
    // The frame attribute specifies which parts of the frame around
    // the table should be rendered. The values are not the same as
    // CALS to avoid a name clash with the valign attribute.
    //-->
    //<!ENTITY % TFrame "(void|above|below|hsides|lhs|rhs|vsides|box|border)">
    //
    //<!--
    // The rules attribute defines which rules to draw between cells:
    //
    // If rules is absent then assume:
    //     "none" if border is absent or border="0" otherwise "all"
    //-->
    //
    //<!ENTITY % TRules "(none | groups | rows | cols | all)">
    //
    //<!-- horizontal placement of table relative to document -->
    //<!ENTITY % TAlign "(left|center|right)">
    //
    //<!-- horizontal alignment attributes for cell contents
    //
    //  char        alignment char, e.g. char=':'
    //  charoff     offset for alignment char
    //-->
    //<!ENTITY % cellhalign
    //  "align      (left|center|right|justify|char) #IMPLIED
    //   char       %Character;    #IMPLIED
    //   charoff    %Length;       #IMPLIED"
    //  >
    //
    //<!-- vertical alignment attributes for cell contents -->
    //<!ENTITY % cellvalign
    //  "valign     (top|middle|bottom|baseline) #IMPLIED"
    //  >
    //
    //<!ELEMENT table
    //     (caption?, (col*|colgroup*), thead?, tfoot?, (tbody+|tr+))>
    //<!ELEMENT caption  %Inline;>
    //<!ELEMENT thead    (tr)+>
    //<!ELEMENT tfoot    (tr)+>
    //<!ELEMENT tbody    (tr)+>
    //<!ELEMENT colgroup (col)*>
    //<!ELEMENT col      EMPTY>
    //<!ELEMENT tr       (th|td)+>
    //<!ELEMENT th       %Flow;>
    //<!ELEMENT td       %Flow;>
    //
    //<!ATTLIST table
    //  %attrs;
    //  summary     %Text;         #IMPLIED
    //  width       %Length;       #IMPLIED
    //  border      %Pixels;       #IMPLIED
    //  frame       %TFrame;       #IMPLIED
    //  rules       %TRules;       #IMPLIED
    //  cellspacing %Length;       #IMPLIED
    //  cellpadding %Length;       #IMPLIED
    //  align       %TAlign;       #IMPLIED
    //  bgcolor     %Color;        #IMPLIED
    //  >
    //
    //<!ENTITY % CAlign "(top|bottom|left|right)">
    //
    //<!ATTLIST caption
    //  %attrs;
    //  align       %CAlign;       #IMPLIED
    //  >
    //
    //<!--
    //colgroup groups a set of col elements. It allows you to group
    //several semantically related columns together.
    //-->
    //<!ATTLIST colgroup
    //  %attrs;
    //  span        %Number;       "1"
    //  width       %MultiLength;  #IMPLIED
    //  %cellhalign;
    //  %cellvalign;
    //  >
    //
    //<!--
    // col elements define the alignment properties for cells in
    // one or more columns.
    //
    // The width attribute specifies the width of the columns, e.g.
    //
    //     width=64        width in screen pixels
    //     width=0.5*      relative width of 0.5
    //
    // The span attribute causes the attributes of one
    // col element to apply to more than one column.
    //-->
    //<!ATTLIST col
    //  %attrs;
    //  span        %Number;       "1"
    //  width       %MultiLength;  #IMPLIED
    //  %cellhalign;
    //  %cellvalign;
    //  >
    //
    //<!--
    //    Use thead to duplicate headers when breaking table
    //    across page boundaries, or for static headers when
    //    tbody sections are rendered in scrolling panel.
    //
    //    Use tfoot to duplicate footers when breaking table
    //    across page boundaries, or for static footers when
    //    tbody sections are rendered in scrolling panel.
    //
    //    Use multiple tbody sections when rules are needed
    //    between groups of table rows.
    //-->
    //<!ATTLIST thead
    //  %attrs;
    //  %cellhalign;
    //  %cellvalign;
    //  >
    //
    //<!ATTLIST tfoot
    //  %attrs;
    //  %cellhalign;
    //  %cellvalign;
    //  >
    //
    //<!ATTLIST tbody
    //  %attrs;
    //  %cellhalign;
    //  %cellvalign;
    //  >
    //
    //<!ATTLIST tr
    //  %attrs;
    //  %cellhalign;
    //  %cellvalign;
    //  bgcolor     %Color;        #IMPLIED
    //  >
    //
    //<!-- Scope is simpler than headers attribute for common tables -->
    //<!ENTITY % Scope "(row|col|rowgroup|colgroup)">
    //
    //<!-- th is for headers, td for data and for cells acting as both -->
    //
    //<!ATTLIST th
    //  %attrs;
    //  abbr        %Text;         #IMPLIED
    //  axis        CDATA          #IMPLIED
    //  headers     IDREFS         #IMPLIED
    //  scope       %Scope;        #IMPLIED
    //  rowspan     %Number;       "1"
    //  colspan     %Number;       "1"
    //  %cellhalign;
    //  %cellvalign;
    //  nowrap      (nowrap)       #IMPLIED
    //  bgcolor     %Color;        #IMPLIED
    //  width       %Length;       #IMPLIED
    //  height      %Length;       #IMPLIED
    //  >
    //
    //<!ATTLIST td
    //  %attrs;
    //  abbr        %Text;         #IMPLIED
    //  axis        CDATA          #IMPLIED
    //  headers     IDREFS         #IMPLIED
    //  scope       %Scope;        #IMPLIED
    //  rowspan     %Number;       "1"
    //  colspan     %Number;       "1"
    //  %cellhalign;
    //  %cellvalign;
    //  nowrap      (nowrap)       #IMPLIED
    //  bgcolor     %Color;        #IMPLIED
    //  width       %Length;       #IMPLIED
    //  height      %Length;       #IMPLIED
    //  >
    //

}