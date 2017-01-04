# Adorable Avatars

[![Version](https://img.shields.io/packagist/v/tfrommen/adorable-avatars.svg)](https://packagist.org/packages/tfrommen/adorable-avatars)
[![Status](https://img.shields.io/badge/status-active-brightgreen.svg)](https://github.com/tfrommen/adorable-avatars)
[![Build](https://img.shields.io/travis/tfrommen/adorable-avatars.svg)](http://travis-ci.org/tfrommen/adorable-avatars)
[![Downloads](https://img.shields.io/packagist/dt/tfrommen/adorable-avatars.svg)](https://packagist.org/packages/tfrommen/adorable-avatars)
[![License](https://img.shields.io/packagist/l/tfrommen/adorable-avatars.svg)](https://packagist.org/packages/tfrommen/adorable-avatars)

> This plugin integrates the [Adorable Avatars](http://avatars.adorable.io/) avatar placeholder service into WordPress.

## Installation

1. [Download ZIP](https://github.com/tfrommen/adorable-avatars/releases).
1. Upload contents to the `/wp-content/plugins` directory on your web server.
1. Activate the plugin through the _Plugins_ menu in WordPress.
1. Select _Adorable Avatars_ as default avatar setting on the _Discussion Settings_ page in your WordPress back end.

## Filters

Need to customize anything? Just use the provided filters.

### `adorable_avatars.force`

In case you want to have Adorable Avatars all over your site (i.e., not only as default when there is no Gravatar), use this filter.

**Arguments:**

* `bool` `$force` Force Adorable Avatars?
* `mixed` `$id_or_email` User identifier.
* `array` `$args` Avatar args.

**Usage Example:**

Use Adorable Avatars no matter what:

```php
<?php

add_filter( 'adorable_avatars.force', '__return_true' );
```

Use Adorable Avatars for anyone but the user with ID 42:

```php
<?php

add_filter( 'adorable_avatars.force', function ( $force, $id_or_email ) {

	if ( is_numeric( $id_or_email ) ) {
		$id_or_email = (int) $id_or_email;
	} elseif ( $id_or_email instanceof WP_Post ) {
		$id_or_email = $id_or_email->ID;
	} elseif ( $id_or_email instanceof WP_Comment ) {
		$id_or_email = $id_or_email->user_id;
	}

	return 42 !== $id_or_email;
}, 10, 2 );
```

## Screenshots

![Setting](resources/assets/screenshot-1.jpg)  
**Default Avatar setting** - Here you can select _Adorable Avatars_ as default avatar setting.

## Contribution

If you have a feature request, or if you have developed the feature already, please feel free to use the Issues and/or Pull Requests section.

Of course, you can also provide me with [translations](https://translate.wordpress.org/projects/wp-plugins/adorable-avatars) if you would like to use the plugin in another not yet included language.

## License

Copyright (c) 2016 Thorsten Frommen

This code is licensed under the [MIT License](LICENSE).
