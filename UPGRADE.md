# Upgrade

This document outlines backward incompatible changes (and other predicted breaking changes) between released versions. All mentioned classes are relative to the namespace `Thunder\Shortcode` unless stated otherwise. Namespaces may be omitted if the class name is unique.

## Version 1.*

* v1.0.0 (dd.mm.yyyy)

  * removed classes and methods deprecated in previous releases,
  * removed all handler-related methods from `Processor` (extracted to `HandlerContainer`):
    * `addHandler()`,
    * `addHandlerAlias()`,
    * `setDefaultHandler()`.
  * `Processor` now requires instance of `HandlerContainer`,
  * refactored `ShortcodeFacade` to also use `HandlerContainer`, also `SyntaxInterface` parameter is now required,
  * `Processor` is now immutable, options setters were refactored to return reconfigured clones:
    * `setRecursionDepth()` &raquo; `withRecursionDepth()`,
    * `setMaxIterations()` &raquo; `withMaxIterations()`,
    * `setAutoProcessContent()` &raquo; `withAutoProcessContent()`.

## Version 0.*

* v0.4.0 (15.07.2015)

  * classes and interfaces were moved to their own namespaces, update your `use` clauses and use new names. Backward compatibility was fully maintained, but note that previous class files will be removed in the next release. Old class files contain call to `class_alias()` and empty implementation for IDE autocompletion, interfaces extend those from new locations. All modified elements are listed below: 
    * `Extractor` &raquo; `Parser\RegexExtractor`,
    * `ExtractorInterface` &raquo; `Extractor\ExtractorInterface`,
    * `HandlerInterface` &raquo; `Extractor\HandlerInterface`,
    * `Parser` &raquo; `Parser\RegexParser`,
    * `ParserInterface` &raquo; `Parser\ParserInterface`,
    * `Processor` &raquo; `Processor\Processor`,
    * `ProcessorInterface` &raquo; `Processor\ProcessorInterface`,
    * `SerializerInterface` &raquo; `Serializer\SerializerInterface`,
    * `Shortcode` &raquo; `Shortcode\Shortcode`,
    * `Syntax` &raquo; `Syntax\Syntax`,
    * `SyntaxBuilder` &raquo; `Syntax\SyntaxBuilder`,
  * next version of this library will remove all files marked as deprecated (listed above) and will introduce backward incompatible changes to allow finishing refactorings for version 1.0. Sneak peek:
    * ~~`Extractor` abstraction will be removed and its functionality will be merged with `Parser`,~~
    * processing shortcode's content will be moved to its handler,
    * `ProcessedShortcode` will be aware of `ProcessorInterface` instance that is processing it,
    * `HandlerContainer` will be refactored outside `Processor` to remove SRP violation,
    * various methods will lose their ability to accept nullable parameters to enforce visibility of dependencies,
    * `ProcessedShortcode` will not extend `Shortcode` and `Shortcode` will be `final` again,
    * ~~`Match` class will be removed and `TextAwareShortcode` will be introduced in its place.~~
  * README was updated to reflect those changes.
