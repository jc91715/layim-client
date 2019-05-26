/**
 * Load the LaravelForm helper class.
 */
require('./form');

/**
 * Define the LaravelFormError collection class.
 */
require('./errors');

/**
 * Add additional HTTP / form helpers to the Laravel object.
 */
$.extend(Laravel, require('./http'));
