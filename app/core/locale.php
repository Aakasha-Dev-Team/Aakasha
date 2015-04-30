<?php
require_once(APP_PATH . "libraries/gettext/gettext.php");
require_once(APP_PATH . "libraries/gettext/streams.php");

//Set local file
$language_file  = new FileReader(APP_PATH . "locale/bg_BG/LC_MESSAGES/messages.mo");

//Get text
$language_fetch = new gettext_reader($language_file);

//Set global
\helpers\globals::set('language_fetch', $language_fetch);

/**
 * Translate text
 * @author Bobi <me@borislazarov.com> on 26 Oct 2014
 * @param  string $text Text for translation
 * @return string       Translated text
 */
function _T($text) {
    $language_fetch = \helpers\globals::get('language_fetch');
    return $language_fetch->translate($text);
}
?>