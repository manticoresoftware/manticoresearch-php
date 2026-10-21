<?php

// Polyfill for PHP versions/environment where CurlHandle class is unavailable (e.g. PHP < 8.0)
if (!class_exists('CurlHandle')) {
	class CurlHandle {}
}
