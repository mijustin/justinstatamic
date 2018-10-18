<?php

namespace Statamic\API;

use Statamic\Config\Settings;

/**
 * Interacting with the configuration
 */
class Config
{
    /**
     * @return Settings
     */
    private static function config()
    {
        return app(Settings::class);
    }

    /**
     * Get a config variable
     *
     * @param string      $key      The name of the key
     * @param mixed|bool  $default  The fallback value
     * @return mixed
     */
    public static function get($key, $default = false)
    {
        return self::config()->get($key, $default);
    }

    /**
     * Set a config variable
     *
     * @param string $key  The name of the key
     * @param mixed $value The value to set
     */
    public static function set($key, $value)
    {
        self::config()->set($key, $value);
    }

    /**
     * Get all config values
     *
     * @return array
     */
    public static function all()
    {
        return self::config()->all();
    }

    /**
     * Save the config
     *
     * @return void
     */
    public static function save()
    {
        self::config()->save();
    }

    /**
     * Get the app key
     *
     * @return string
     */
    public static function getAppKey()
    {
        return self::get('system.app_key');
    }

    /**
     * Get the license key
     *
     * @return string|null
     */
    public static function getLicenseKey()
    {
        $key = self::get('system.license_key');

        if (! $key || $key == '') {
            return null;
        }

        return $key;
    }

    /**
     * Get the active theme name
     *
     * @return string
     */
    public static function getThemeName()
    {
        return self::get('theming.theme', 'default');
    }

    /**
     * Get the current locale's full code for date string translations
     *
     * @param string|null $locale
     * @return string
     */
    public static function getFullLocale($locale = null)
    {
        if (is_null($locale)) {
            $locale = LOCALE;
        }

        return self::get('system.locales.' . $locale . '.full', 'en_US');
    }

    /**
     * Get the current locale's short code
     *
     * @param string|null $locale
     * @return string
     */
    public static function getShortLocale($locale = null)
    {
        $full = str_replace('_', '-', self::getFullLocale($locale));

        return explode('-', $full)[0];
    }

    /**
     * Get the current locale's name
     *
     * @param string|null $locale
     * @return string
     */
    public static function getLocaleName($locale = null)
    {
        if (is_null($locale)) {
            $locale = LOCALE;
        }

        return self::get('system.locales.' . $locale . '.name', 'English');
    }

    /**
     * Get the locale keys
     *
     * @return array
     */
    public static function getLocales()
    {
        return array_keys(self::get('system.locales'));
    }

    /**
     * Get the default locale
     *
     * @return mixed
     */
    public static function getDefaultLocale()
    {
        if (env('APP_ENV') === 'testing') {
            return 'en';
        }

        $locales = self::get('system.locales');

        return key($locales);
    }

    /**
     * Get the locales that aren't the current (or specified) one
     *
     * @param string|null $locale The locale to treat as the current one
     * @return array
     */
    public static function getOtherLocales($locale = null)
    {
        if (! $locale) {
            $locale = site_locale();
        }

        $locales = array_keys(self::get('system.locales'));

        return array_values(array_diff($locales, [$locale]));
    }

    /**
     * Get the site URL
     *
     * @param string|null $locale Optionally get the site url for a locale
     * @return mixed
     */
    public static function getSiteUrl($locale = null)
    {
        $locales = self::get('system.locales');

        $locale = $locale ?: site_locale();

        return Str::ensureRight(array_get($locales, $locale.'.url'), '/');
    }

    /**
     * Get routes
     *
     * @return array
     */
    public static function getRoutes()
    {
        return self::get('routes');
    }

    /**
     * Get the image manipulation presets
     *
     * @return array
     */
    public static function getImageManipulationPresets()
    {
        return self::get('assets.image_manipulation_presets', []);
    }
}
