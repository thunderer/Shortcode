# Upgrade

## Version 0.*

* v0.4.0 (dd.mm.2015)

  * nearly all classes and interfaces were moved to their own namespaces, update your `use` clauses and use new names. Backward compatibility was fully maintained, but note that previous class files will be removed in the next release. Old class files contain call to `class_alias()` and empty implementation for IDE autocompletion, interfaces extend those from new locations. All modified elements are listed below:
    * `Thunder\Shortcode\Extractor` &raquo; `Thunder\Shortcode\Parser\RegexExtractor`,
    * `Thunder\Shortcode\ExtractorInterface` &raquo; `Thunder\Shortcode\Extractor\ExtractorInterface`,
    * `Thunder\Shortcode\HandlerInterface` &raquo; `Thunder\Shortcode\Extractor\HandlerInterface`,
    * `Thunder\Shortcode\Parser` &raquo; `Thunder\Shortcode\Parser\RegexParser`,
    * `Thunder\Shortcode\ParserInterface` &raquo; `Thunder\Shortcode\Parser\ParserInterface`,
    * `Thunder\Shortcode\Processor` &raquo; `Thunder\Shortcode\Processor\Processor`,
    * `Thunder\Shortcode\ProcessorInterface` &raquo; `Thunder\Shortcode\Processor\ProcessorInterface`,
    * `Thunder\Shortcode\SerializerInterface` &raquo; `Thunder\Shortcode\Serializer\SerializerInterface`,
    * `Thunder\Shortcode\Shortcode` &raquo; `Thunder\Shortcode\Shortcode\Shortcode`, 
    * `Thunder\Shortcode\Syntax` &raquo; `Thunder\Shortcode\Syntax\Syntax`,
    * `Thunder\Shortcode\SyntaxBuilder` &raquo; `Thunder\Shortcode\Syntax\SyntaxBuilder`,
  * next version of this library will remove all files marked as deprecated (listed above) and will introduce backward incompatible changes to allow finishing refactorings for version 1.0. Sneak peek:
    * `Extractor` abstraction will be removed and its functionality will be merged with `Parser`,
    * processing shortcode's content will be moved to its handler,
    * `ContextAwareShortcode` will be aware of `ProcessorInterface` instance that is processing it,
    * `HandlerContainer` will be refactored outside `Processor` to remove SRP violation,
    * various methods will lose their ability to accept nullable parameters to enforce visibility of dependencies,
    * `ContextAwareShortcode` will not extend `Shortcode` and `Shortcode` will be `final` again,
    * `Match` class will be removed and `TextAwareShortcode` will be introduced in its place.
  * README was updated to reflect those changes.
