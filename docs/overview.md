# Overview

The Filter package cleans data. `InputFilter` sanitises values coming **in** — it backs every
`Joomla\Input\Input::get()` call — and `OutputFilter` prepares strings going **out**.

```bash
composer require joomla/filter
```

## `InputFilter`

### Filtering by type

`clean($source, $type)` returns the value coerced and cleaned according to `$type`:

```php
use Joomla\Filter\InputFilter;

$filter = new InputFilter();

$filter->clean($value, 'int');       // first integer found, or 0
$filter->clean($value, 'uint');      // as above, absolute
$filter->clean($value, 'float');     // first float found, or 0.0
$filter->clean($value, 'bool');      // (bool) cast
$filter->clean($value, 'word');      // A-Z and underscore only
$filter->clean($value, 'alnum');     // A-Z and 0-9 only
$filter->clean($value, 'cmd');       // A-Z, 0-9, _ . - with leading dots stripped
$filter->clean($value, 'base64');    // base64 alphabet
$filter->clean($value, 'string');    // entity-decoded, then tags stripped
$filter->clean($value, 'html');      // tags stripped, entities left alone
$filter->clean($value, 'path');      // a restricted file path, or ''
$filter->clean($value, 'trim');      // trimmed, including non-breaking spaces
$filter->clean($value, 'array');     // (array) cast
$filter->clean($value, 'raw');       // returned unchanged
```

An unknown type falls through to the `string` behaviour. Arrays are filtered element-wise;
objects are filtered **in place** and returned, so the caller's object is modified.

### Filtering HTML

The constructor configures what survives the tag and attribute filters:

```php
// Allow only these tags and attributes, drop everything else.
$filter = new InputFilter(
    ['p', 'strong', 'em', 'a'],
    ['href', 'title'],
    InputFilter::ONLY_ALLOW_DEFINED_TAGS,
    InputFilter::ONLY_ALLOW_DEFINED_ATTRIBUTES
);

$safe = $filter->clean($userHtml, 'html');
```

The `$tagsMethod` and `$attrMethod` constants invert the meaning:

| Constant | Value | Meaning |
|---|---|---|
| `ONLY_ALLOW_DEFINED_TAGS` | `0` | The list is an allowlist |
| `ONLY_BLOCK_DEFINED_TAGS` | `1` | The list is a blocklist |
| `ONLY_ALLOW_DEFINED_ATTRIBUTES` | `0` | The list is an allowlist |
| `ONLY_BLOCK_DEFINED_ATTRIBUTES` | `1` | The list is a blocklist |

The default — empty lists with both methods set to allow — strips every tag, which is the safe
default.

The fifth constructor argument, `$xssAuto`, defaults to `1` and enables the built-in blocklists for
tags such as `script`, `iframe`, `object` and `style`, and for `on*` event handler attributes.
Do not turn it off.

### A caution about the HTML filter

`InputFilter` parses HTML with string operations rather than a parser, which means it makes
different assumptions about the document than the browser that will render it. That gap is where
sanitiser bypasses live, and this implementation has known ones:

* **URL schemes are not checked beyond a few names.** `javascript:`, `vbscript:`, `livescript:`
  and `mocha:` are blocked; `data:` is **not**. If `a`/`href` or `img`/`src` is on your allowlist,
  `<a href="data:text/html;base64,…">` gets through.
* Attributes without a value — `checked`, `disabled`, `required` — are always dropped, even when
  explicitly allowed.

For untrusted HTML from users, prefer a parser-based sanitiser such as
[`ezyang/htmlpurifier`](https://github.com/ezyang/htmlpurifier) or
[`symfony/html-sanitizer`](https://symfony.com/doc/current/html_sanitizer.html), and keep
`InputFilter` for the scalar types, where it is doing simple, predictable work.

### The scalar filters are extraction, not validation

`cleanInt()` and friends search for the first match anywhere in the string:

```php
$filter->clean('abc123', 'int');    // 123, not 0
$filter->clean('x-5y', 'int');      // -5
```

So a filtered value is never a statement that the input was well-formed. Validate separately when
that matters:

```php
$id = $input->getUint('id');

if ($id === 0 || (string) $id !== $input->get('id', '', 'raw')) {
    throw new \InvalidArgumentException('id must be a positive integer');
}
```

The same applies to `path`: it accepts absolute paths (`/etc/passwd` passes) and returns `''` for
anything containing a space or a non-ASCII character, so it neither confines a path to a directory
nor round-trips ordinary filenames.

## `OutputFilter`

```php
use Joomla\Filter\OutputFilter;

OutputFilter::objectHtmlSafe($object);              // htmlspecialchars every scalar property, by reference
OutputFilter::stringUrlSafe('Ein schöner Titel');   // 'ein-schoner-titel'
OutputFilter::stringUrlUnicodeSlug('Ein Titel');    // keeps unicode characters
OutputFilter::stringJSSafe($string);                // \uXXXX-escapes every character
OutputFilter::ampReplace($html);                    // bare & to &amp;amp;
OutputFilter::linkXhtmlSafe($html);                 // & inside href to &amp;amp;
OutputFilter::cleanText($text);                     // strip markup, then escape
OutputFilter::stripImages($html);
OutputFilter::stripIframes($html);
```

`stringUrlSafe()` transliterates. Without a `Language` instance it uses the built-in en-GB
transliteration; give it one for language-specific rules:

```php
OutputFilter::setLanguage($language);
OutputFilter::stringUrlSafe($title, 'de-DE');
```

That setter stores the language **statically**, so it affects every later call in the process.

### What `OutputFilter` does not give you

There is no general-purpose escaping method for a string. `objectHtmlSafe()` handles objects, and
`stringJSSafe()` handles JavaScript, but for the ordinary case — escaping one value for HTML — use
PHP directly:

```php
echo htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
```

Note also that `cleanText()` escapes with `ENT_COMPAT`, which leaves single quotes untouched — its
output is not safe inside a single-quoted attribute. And `stripImages()`/`stripIframes()` match
without the `s` modifier, so a tag containing a newline is not removed; treat them as cosmetic, not
as a security control.

## Where this package is used

`joomla/input` requires it and applies `cmd` by default to every `Input::get()`. Reading a value
with a deliberate filter is usually better than relying on that default:

```php
$title = $input->getString('title');
$id    = $input->getUint('id');
$html  = $input->get('body', '', 'html');
$raw   = $input->get('payload', '', 'raw');
```
