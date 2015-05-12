<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
class_alias('Thunder\\Shortcode\\Syntax\\SyntaxBuilder', 'Thunder\\Shortcode\\SyntaxBuilder', true);
return;

/**
 * Weird, PHP does not see the aliased class and does not honor return statement,
 * SyntaxBuilder declaration needs to be wrapped as shown below to work...
 */
if(false)
    {
    /**
     * This implementation is left only to not break IDE autocompletion, this class
     * is deprecated, it was moved to the new location as specified in docblock.
     * This file will be removed in version 1.0!
     *
     * @deprecated use Thunder\Shortcode\Syntax\SyntaxBuilder
     * @codeCoverageIgnore
     */
    class SyntaxBuilder
        {
        public function __construct()
            {
            }

        public function getSyntax()
            {
            return new Syntax();
            }

        /**
         * @deprecated
         */
        public function setStrict($isStrict)
            {
            return $this;
            }

        public function setOpeningTag($tag)
            {
            return $this;
            }

        public function setClosingTag($tag)
            {
            return $this;
            }

        public function setClosingTagMarker($marker)
            {
            return $this;
            }

        public function setParameterValueSeparator($separator)
            {
            return $this;
            }

        public function setParameterValueDelimiter($delimiter)
            {
            return $this;
            }
        }
    }
