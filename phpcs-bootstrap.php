<?php
/**
 * PHPCS bootstrap — defines ABSPATH so the ABSPATH guard in helper files
 * does not exit before PHP_CodeSniffer can scan the source files.
 * Not loaded during normal WordPress execution.
 */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
