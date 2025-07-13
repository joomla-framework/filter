# The Filter Package [![Build Status](https://github.com/joomla-framework/filter/actions/workflows/ci.yml/badge.svg?branch=3.x-dev)](https://github.com/joomla-framework/filter)

[![Latest Stable Version](https://poser.pugx.org/joomla/filter/v/stable)](https://packagist.org/packages/joomla/filter)
[![Total Downloads](https://poser.pugx.org/joomla/filter/downloads)](https://packagist.org/packages/joomla/filter)
[![Latest Unstable Version](https://poser.pugx.org/joomla/filter/v/unstable)](https://packagist.org/packages/joomla/filter)
[![License](https://poser.pugx.org/joomla/filter/license)](https://packagist.org/packages/joomla/filter)

## Installation via Composer

Add `"joomla/filter": "~4.0"` to the require block in your composer.json and then run `composer install`.

```json
{
	"require": {
		"joomla/filter": "~4.0"
	}
}
```

Alternatively, you can simply run the following from the command line:

```sh
composer require joomla/filter "~4.0"
```

If you want to include the test sources, use

```sh
composer require --prefer-source joomla/filter "~4.0"
```

Note that the `Joomla\Language` package is an optional dependency and is only required if the application requires the use of `OutputFilter::stringURLSafe`.
