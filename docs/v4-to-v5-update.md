# Updating from v4 to v5

The following changes were made to the Filter package between v4 and v5.

## Native parameter, return and property types

`InputFilter` and `OutputFilter` now declare native types everywhere:

| Method / property | v5 signature |
|---|---|
| `InputFilter::__construct()` | `(array $tagsArray = [], array $attrArray = [], int $tagsMethod = …, int $attrMethod = …, int $xssAuto = 1)` |
| `InputFilter::clean()` | `(mixed $source, string $type = 'string'): mixed` |
| `InputFilter::checkAttribute()` | `(array $attrSubSet): bool` |
| `InputFilter::remove()`, `cleanTags()`, `escapeAttributeValues()`, `stripCssExpressions()` (protected) | `(string $source): string` |
| `InputFilter::cleanAttributes()` (protected) | `(array $attrSet): array` |
| `$tagsArray`, `$attrArray`, `$blockedTags`, `$blockedAttributes` | `array` |
| `$tagsMethod`, `$attrMethod`, `$xssAuto` | `int` |
| `OutputFilter::objectHtmlSafe()` | `(object $mixed, ?int $quoteStyle = ENT_QUOTES, string\|array $excludeKeys = ''): void` |
| `OutputFilter::cleanText()` | `(?string $text): string` |
| `OutputFilter::linkXhtmlSafe()`, `stringJSSafe()`, `stringUrlUnicodeSlug()`, `ampReplace()`, `stripImages()`, `stripIframes()` | `(string …): string` |
| `OutputFilter::stringUrlSafe()` | `(string $string, string $language = ''): string` |

Subclasses that override these methods must add the same return types; overriding methods may keep
untyped parameters. Assigning a value of another type to the typed properties (for example
`$filter->xssAuto = true`) is coerced or throws a `TypeError` under `strict_types`.

## `InputFilter::clean()` no longer modifies objects

Objects are filtered on a copy, and the copy is returned:

```php
// v4: $object itself was cleaned
$filter->clean($object, 'string');

// v5: use the return value
$object = $filter->clean($object, 'string');
```

## `OutputFilter::cleanText()` takes its argument by value

`cleanText()` no longer changes the variable passed to it. Use the return value:

```php
// v4
OutputFilter::cleanText($text);

// v5
$text = OutputFilter::cleanText($text);
```

`objectHtmlSafe()` takes the object by value as well; as objects are handles, it still escapes the
properties of the object passed in. Passing anything but an object is now a `TypeError` instead of
being ignored.

## `id` and `name` removed from `InputFilter::$blockedTags`

These are attribute names, not tags, so their entries in the tag blocklist had no effect on
attributes. With `ONLY_BLOCK_DEFINED_TAGS`, `<id>` and `<name>` elements are no longer removed.
