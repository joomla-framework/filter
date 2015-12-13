# The Filter Package [![Build Status](https://travis-ci.org/joomla-framework/filter.png?branch=master)](https://travis-ci.org/joomla-framework/filter)

[![Latest Stable Version](https://poser.pugx.org/joomla/filter/v/stable)](https://packagist.org/packages/joomla/filter)
[![Total Downloads](https://poser.pugx.org/joomla/filter/downloads)](https://packagist.org/packages/joomla/filter)
[![Latest Unstable Version](https://poser.pugx.org/joomla/filter/v/unstable)](https://packagist.org/packages/joomla/filter)
[![License](https://poser.pugx.org/joomla/filter/license)](https://packagist.org/packages/joomla/filter)

## Installation via Composer

Add `"joomla/filter": "2.0.*@dev"` to the require block in your composer.json and then run `composer install`.

```json
{
	"require": {
		"joomla/filter": "2.0.*@dev"
	}
}
```

Alternatively, you can simply run the following from the command line:

```sh
composer require joomla/filter "2.0.*@dev"
```

Note that the `Joomla\Language` package is an optional dependency and is only required if the application requires the use of `OutputFilter::stringURLSafe`.
