# Updating from v4 to v5

The following changes were made to the Filter package between v4 and v5.

## `OutputFilter::stringUrlUnicodeSlug()` removes commas

Commas are now treated like the other forbidden characters and replaced with a hyphen:

```php
OutputFilter::stringUrlUnicodeSlug('a, b'); // v4: 'a,-b', v5: 'a-b'
```

`stringUrlSafe()` already removed them. Only newly generated slugs change, but code that looks up
an existing record by regenerating its slug from a title containing a comma no longer finds it.
Store the slug, or fall back to the v4 form for existing records.
