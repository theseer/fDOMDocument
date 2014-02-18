<?php
namespace TheSeer\fDOM\CSS {

    class Translator {

        /**
         * @var array
         */
        private $rules;

        /**
         * @param string $selector A CSS Selector string
         *
         * @return string
         */
        public function translate($selector) {
            foreach($this->getRules() as $rule) {
                /** @var RuleInterface $rule */
                $selector = $rule->apply($selector);
            }
            return '//' . $selector;
        }

        /**
         * @return array
         */
        private function getRules() {
            if ($this->rules != NULL) {
                return $this->rules;
            }

            $this->rules = array(

                // prefix|name
                new RegexRule('/([a-zA-Z0-9\_\-\*]+)\|([a-zA-Z0-9\_\-\*]+)/', '$1:$2'),

                // add @ for attribs
                new RegexRule("/\[([^\]~\$\*\^\|\!]+)(=[^\]]+)?\]/", '[@$1$2]'),

                // multiple queries
                new RegexRule("/\s*,\s*/", '|'),

                // , + ~ >
                new RegexRule("/\s*(\+|~|>)\s*/", '$1'),

                //* ~ + >
                new RegexRule("/([a-zA-Z0-9\_\-\*])~([a-zA-Z0-9\_\-\*])/", '$1/following-sibling::$2'),
                new RegexRule("/([a-zA-Z0-9\_\-\*])\+([a-zA-Z0-9\_\-\*])/", '$1/following-sibling::*[1]/self::$2'),
                new RegexRule("/([a-zA-Z0-9\_\-\*])>([a-zA-Z0-9\_\-\*])/", '$1/$2'),

                // all unescaped stuff escaped
                new RegexRule("/\[([^=]+)=([^'|'][^\]]*)\]/", '[$1="$2"]'),

                // all descendant or self to //
                new RegexRule("/(^|[^a-zA-Z0-9\_\-\*])(#|\.)([a-zA-Z0-9\_\-]+)/", '$1*$2$3'),
                new RegexRule("/([\>\+\|\~\,\s])([a-zA-Z\*]+)/", '$1//$2'),
                new RegexRule("/\s+\/\//", '//'),

                // :first-child
                new RegexRule("/([a-zA-Z0-9\_\-\*]+):first-child/", '*[1]/self::$1'),

                // :last-child
                new RegexRule("/([a-zA-Z0-9\_\-\*]+):last-child/", '$1[not(following-sibling::*)]'),

                // :only-child
                new RegexRule("/([a-zA-Z0-9\_\-\*]+):only-child/", '*[last()=1]/self::$1'),

                // :empty
                new RegexRule("/([a-zA-Z0-9\_\-\*]+):empty/", '$1[not(*) and not(normalize-space())]'),

                // :not
                new NotRule($this),

                // :nth-child
                new NthChildRule(),

                // :contains(selectors)
                new RegexRule('/:contains\(([^\)]*)\)/', '[contains(string(.),"$1")]'),

                // |= attrib
                new RegexRule("/\[([a-zA-Z0-9\_\-]+)\|=([^\]]+)\]/", '[@$1=$2 or starts-with(@$1,concat($2,"-"))]'),

                // *= attrib
                new RegexRule("/\[([a-zA-Z0-9\_\-]+)\*=([^\]]+)\]/", '[contains(@$1,$2)]'),

                // ~= attrib
                new RegexRule("/\[([a-zA-Z0-9\_\-]+)~=([^\]]+)\]/", '[contains(concat(" ",normalize-space(@$1)," "),concat(" ",$2," "))]'),

                // ^= attrib
                new RegexRule("/\[([a-zA-Z0-9\_\-]+)\^=([^\]]+)\]/", '[starts-with(@$1,$2)]'),

                // $= attrib
                new DollarEqualRule(),

                // != attrib
                new RegexRule("/\[([a-zA-Z0-9\_\-]+)\!=([^\]]+)\]/", '[not(@$1) or @$1!=$2]'),

                // ids and classes
                new RegexRule("/#([a-zA-Z0-9\_\-]+)/", '[@id="$1"]'),
                new RegexRule("/\.([a-zA-Z0-9\_\-]+)/", '[contains(concat(" ",normalize-space(@class)," ")," $1 ")]'),

                // normalize multiple filters
                new RegexRule("/\]\[([^\]]+)/", ' and ($1)')


            );
            return $this->rules;
        }
    }

}
