<?php

namespace Oxy86\PhpWeatherKit\Validation\Rules;

use Oxy86\PhpWeatherKit\Validation\Rules\Rule;


class LanguageRule implements Rule
{

  /**
   * {@inheritdoc}
   */
  public function passes($value)
  {
    return !empty($value) && is_string($value)
      && in_array($value, self::supportedLanguages());
  }

  /**
   * {@inheritdoc}
   */
  public function message()
  {
    return 'The given language is invalid.';
  }


  /**
   * Get the supported languages.
   *
   * @return array
   */
  public static function supportedLanguages()
  {
    return [
      'ar', 'az', 'be', 'bg', 'bs', 'ca', 'cs', 'da', 'de', 'el', 'en', 'es', 'et', 'fi', 'fr',
      'he', 'hr', 'hu', 'id', 'is', 'it', 'ja', 'ka', 'ko', 'kw', 'lv', 'nb', 'nl', 'no', 'pl',
      'pt', 'ro', 'ru', 'sk', 'sl', 'sr', 'sv', 'tet', 'tr', 'uk', 'x-pig-latin', 'zh', 'zh-tw',
    ];
  }
}
