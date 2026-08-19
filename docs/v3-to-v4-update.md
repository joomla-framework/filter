# Updating from v3 to v4

Release 4.0.0 raises the PHP requirement and removes one deprecated protected method.

## At a glance

| | v3 (3.0.3) | v4 (4.0.0) |
|---|---|---|
| PHP | `^8.1.0` | `^8.3.0` |
| `InputFilter::decode()` | deprecated, works | **removed** |
| Public API | — | unchanged |

## Minimum supported PHP version raised

All Framework packages now require **PHP 8.3** or newer.

## `InputFilter::decode()` was removed

The protected helper was a one-line wrapper deprecated since the PHP 5.3 era:

```php
// Removed in 4.0.0
protected function decode($source)
{
    return html_entity_decode($source, \ENT_QUOTES, 'UTF-8');
}
```

Because it was `protected`, this only affects subclasses of `InputFilter` that called or overrode
it. Call the function directly:

```php
// Before
$plain = $this->decode($source);

// After
$plain = html_entity_decode($source, \ENT_QUOTES, 'UTF-8');
```

To find the call sites:

```bash
grep -rn -- '->decode(' src/
```

No public method changed, so code that only uses `clean()` needs no work.

## Dependency changes

| Package | v3 (3.0.3) | v4 (4.0.0) |
|---|---|---|
| `php` | `^8.1.0` | `^8.3.0` |
| `joomla/string` | `^3.0` | `^4.0` |

`joomla/language` remains optional and moved to `^4.0`.
