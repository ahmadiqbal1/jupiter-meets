<?php

use App\Models\GlobalConfig;
use App\Models\Content;
use App\Models\Currency;

//get settings from the global config table
function getSetting($key) {
	$settings = Cache::rememberForever('settings', function() {
	    return GlobalConfig::all()->pluck('value', 'key');
	});

	return $settings[$key];
}

//get content from the content table
function getContent($key) {
	$content = Cache::rememberForever('content', function() {
	    return Content::all()->pluck('value', 'key');
	});

	return $content[$key];
} 

//get currency symbol from the selected currency
function getCurrencySymbol() {
	return Cache::rememberForever('symbol', function() {
	    return Currency::where('code', getSetting('CURRENCY'))->first()->symbol;
	});
}

//set value
function installed ($value) {
	session(['installed' => $value]);
}

//get value
function isInstalled () {
	return session('installed');
}