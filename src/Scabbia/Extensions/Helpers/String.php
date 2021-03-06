<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Helpers;

use Scabbia\Framework;

if (!defined('ENT_HTML5')) {
    /**
     * @ignore
     */
    define('ENT_HTML5', (16 | 32));
}

/**
 * Helpers Extension: String Class
 *
 * @package Scabbia
 * @subpackage Helpers
 * @version 1.1.0
 *
 * @todo pluralize, singularize
 * @todo split Text functions into another file
 * @todo alternator, camel2underscore, underscore2camel
 */
class String
{
    /**
     * @ignore
     */
    // const FILTER_RECURSIVE = ;
    /**
     * @ignore
     */
    const FILTER_VALIDATE_BOOLEAN = 'scabbiaFilterValidateBoolean';
    /**
     * @ignore
     */
    const FILTER_SANITIZE_BOOLEAN = 'scabbiaFilterSanitizeBoolean';
    /**
     * @ignore
     */
    const FILTER_SANITIZE_XSS = 'scabbiaFilterSanitizeXss';
    /**
     * @ignore
     */
    const BASECONVERT_URL_CHARACTERS =
        'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~:[]@!$\'()*+,;';
    /**
     * @ignore
     */
    const BASECONVERT_BASE62_CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';


    /**
     * @ignore
     */
    public static $tab = "\t";


    /**
     * @ignore
     */
    public static function getEncoding()
    {
        return mb_preferred_mime_name(mb_internal_encoding());
    }

    /**
     * @ignore
     */
    public static function coalesce()
    {
        foreach (func_get_args() as $tValue) {
            if ($tValue !== null) {
                if (is_array($tValue)) {
                    if (isset($tValue[0][$tValue[1]]) && $tValue[0][$tValue[1]] !== null) {
                        return $tValue[0][$tValue[1]];
                    }

                    continue;
                }

                return $tValue;
            }
        }

        return null;
    }

    /**
     * @ignore
     */
    public static function prefixLines($uInput, $uPrefix = '- ', $uLineEnding = PHP_EOL)
    {
        $tLines = explode($uLineEnding, $uInput);

        $tOutput = $tLines[0] . $uLineEnding;
        $tCount = 0;
        foreach ($tLines as $tLine) {
            if ($tCount++ === 0) {
                continue;
            }

            $tOutput .= $uPrefix . $tLine . $uLineEnding;
        }

        return $tOutput;
    }

    /**
     * @ignore
     *
     * @todo recursive filtering option
     */
    public static function filter($uValue, $uFilter)
    {
        if ($uFilter === self::FILTER_VALIDATE_BOOLEAN) {
            if ($uValue === true || $uValue === 'true' || $uValue === 1 || $uValue === '1' ||
                $uValue === false || $uValue === 'false' || $uValue === 0 || $uValue === '0') {
                return true;
            }

            return false;
        }

        if ($uFilter === self::FILTER_SANITIZE_BOOLEAN) {
            if ($uValue === true || $uValue === 'true' || $uValue === 1 || $uValue === '1') {
                return true;
            }

            return false;
        }

        if ($uFilter === self::FILTER_SANITIZE_XSS) {
            return self::xss($uValue);
        }

        $uArgs = func_get_args();

        if (is_callable($uFilter, true)) {
            $uArgs[1] = $uValue;
            return call_user_func_array($uFilter, array_slice($uArgs, 1));
        }

        return call_user_func_array('filter_var', $uArgs);
    }

    /**
     * @ignore
     */
    public static function format($uString)
    {
        $uArgs = func_get_args();
        array_shift($uArgs);

        if (count($uArgs) > 0 && is_array($uArgs[0])) {
            $uArgs = $uArgs[0];
        }

        $tBrackets = array(array(null, ""));
        $tQuoteChar = false;
        $tLastItem = 0;
        $tArrayItem = 1;

        for ($tPos = 0, $tLen = self::length($uString); $tPos < $tLen; $tPos++) {
            $tChar = self::substr($uString, $tPos, 1);

            if ($tChar === '\\') {
                $tBrackets[$tLastItem][$tArrayItem] .= self::substr($uString, ++$tPos, 1);
                continue;
            }

            if ($tQuoteChar === false && $tChar === '{') {
                ++$tLastItem;
                $tBrackets[$tLastItem] = array(null, null);
                $tArrayItem = 1;
                continue;
            }

            if ($tLastItem > 0) {
                if ($tBrackets[$tLastItem][$tArrayItem] === null) {
                    if ($tChar === '\'' || $tChar === '"') {
                        $tQuoteChar = $tChar;
                        $tBrackets[$tLastItem][$tArrayItem] = '"'; // static text
                        $tChar = self::substr($uString, ++$tPos, 1);
                    } else {
                        if ($tChar === '!') {
                            $tBrackets[$tLastItem][$tArrayItem] = '!'; // dynamic text
                            $tChar = self::substr($uString, ++$tPos, 1);
                        } else {
                            if ($tChar === '@') {
                                $tBrackets[$tLastItem][$tArrayItem] = '@'; // parameter
                                $tChar = self::substr($uString, ++$tPos, 1);
                            } else {
                                $tBrackets[$tLastItem][$tArrayItem] = '@'; // parameter
                            }
                        }
                    }
                }

                if (self::substr($tBrackets[$tLastItem][$tArrayItem], 0, 1) === '"') {
                    if ($tQuoteChar === $tChar) {
                        $tQuoteChar = false;
                        continue;
                    }

                    if ($tQuoteChar !== false) {
                        $tBrackets[$tLastItem][$tArrayItem] .= $tChar;
                        continue;
                    }

                    if ($tChar !== ',' && $tChar !== '}') {
                        continue;
                    }
                }

                if ($tArrayItem === 1 && $tChar === '|' && $tBrackets[$tLastItem][0] === null) {
                    $tBrackets[$tLastItem][0] = $tBrackets[$tLastItem][1];
                    $tBrackets[$tLastItem][1] = null;
                    continue;
                }

                if ($tChar === ',') {
                    $tBrackets[$tLastItem][++$tArrayItem] = null;
                    continue;
                }

                if ($tChar === '}') {
                    $tFunc = array_shift($tBrackets[$tLastItem]);
                    foreach ($tBrackets[$tLastItem] as &$tItem) {
                        if ($tItem[0] === '"') {
                            $tItem = self::substr($tItem, 1);
                        } elseif ($tItem[0] === '@') {
                            $tItem = $uArgs[self::substr($tItem, 1)];
                        } elseif ($tItem[0] === '!') {
                            $tItem = constant(self::substr($tItem, 1));
                        }
                    }

                    if ($tFunc !== null) {
                        $tString = call_user_func_array(self::substr($tFunc, 1), $tBrackets[$tLastItem]);
                    } else {
                        $tString = implode(', ', $tBrackets[$tLastItem]);
                    }

                    $tArrayItem = count($tBrackets[$tLastItem - 1]) - 1;
                    $tBrackets[$tLastItem - 1][$tArrayItem] .= $tString;
                    unset($tBrackets[$tLastItem]);
                    $tLastItem--;

                    continue;
                }
            }

            $tBrackets[$tLastItem][$tArrayItem] .= $tChar;
        }

        return $tBrackets[0][1];
    }

    /**
     * @ignore
     */
    public static function vardump($uVariable, $tOutput = true)
    {
        $tVariable = $uVariable;
        $tType = gettype($tVariable);
        $tOut = "";
        static $sTabs = "";

        if ($tType === 'boolean') {
            $tOut .= '<b>boolean</b>(' . (($tVariable) ? 'true' : 'false') . ')' . PHP_EOL;
        } elseif ($tType === 'double') {
            $tOut .= '<b>' . $tType . '</b>(\'' . number_format($tVariable, 22, '.', '') . '\')' . PHP_EOL;
        } elseif ($tType === 'integer' || $tType === 'string') {
            $tOut .= '<b>' . $tType . '</b>(\'' . $tVariable . '\')' . PHP_EOL;
        } elseif ($tType === 'array' || $tType === 'object') {
            if ($tType === 'object') {
                $tType = get_class($tVariable);
                $tVariable = get_object_vars($tVariable);
            }

            $tCount = count($tVariable);
            $tOut .= '<b>' . $tType . '</b>(' . $tCount . ')';

            if ($tCount > 0) {
                $tOut .= ' {' . PHP_EOL;

                $sTabs .= self::$tab;
                foreach ($tVariable as $tKey => $tVal) {
                    $tOut .= $sTabs . '[' . $tKey . '] = ';
                    $tOut .= self::vardump($tVal, false);
                }
                $sTabs = substr($sTabs, 0, -1);

                $tOut .= $sTabs . '}';
            }

            $tOut .= PHP_EOL;
        } elseif ($tType === 'resource') {
            $tOut .= '<b>resource</b>(\'' . get_resource_type($tVariable) . '\')' . PHP_EOL;
        } elseif ($tType === 'NULL') {
            $tOut .= '<b><i>null</i></b>' . PHP_EOL;
        } else {
            $tOut .= '<b>' . $tType . '</b>' . PHP_EOL;
        }

        if ($tOutput) {
            echo '<pre>', $tOut, '</pre>';

            return null;
        }

        return $tOut;
    }

    /**
     * @ignore
     */
    public static function hash($uHash)
    {
        return hexdec(hash('crc32', $uHash) . hash('crc32b', $uHash));
    }

    /**
     * @ignore
     */
    public static function generatePassword($uLength)
    {
        srand(Framework::$timestamp * 1000000);

        static $sVowels = array('a', 'e', 'i', 'o', 'u');
        static $sCons = array(
            'b',
            'c',
            'd',
            'g',
            'h',
            'j',
            'k',
            'l',
            'm',
            'n',
            'p',
            'r',
            's',
            't',
            'u',
            'v',
            'w',
            'tr',
            'cr',
            'br',
            'fr',
            'th',
            'dr',
            'ch',
            'ph',
            'wr',
            'st',
            'sp',
            'sw',
            'pr',
            'sl',
            'cl'
        );

        $tConsLen = count($sCons) - 1;
        $tVowelsLen = count($sVowels) - 1;
        for ($tOutput = ""; strlen($tOutput) < $uLength;) {
            $tOutput .= $sCons[rand(0, $tConsLen)] . $sVowels[rand(0, $tVowelsLen)];
        }

        // prevent overflow of size
        return substr($tOutput, 0, $uLength);
    }

    /**
     * @ignore
     */
    public static function generateUuid()
    {
        if (function_exists('com_create_guid')) {
            return strtolower(trim(com_create_guid(), '{}'));
        }

        // return md5(uniqid(mt_rand(), true));
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * @ignore
     */
    public static function generate($uLength, $uCharset = '0123456789ABCDEF')
    {
        srand(Framework::$timestamp * 1000000);

        $tCharsetLen = self::length($uCharset) - 1;
        for ($tOutput = ""; $uLength > 0; $uLength--) {
            $tOutput .= self::substr($uCharset, rand(0, $tCharsetLen), 1);
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function xss($uString)
    {
        if (!is_string($uString)) {
            return $uString;
        }

        return str_replace(
            array(
                '<',
                '>',
                '"',
                '\'',
                '$',
                '(',
                ')',
                '%28',
                '%29'
            ),
            array(
                '&#60;',
                '&#62;',
                '&#34;',
                '&#39;',
                '&#36;',
                '&#40;',
                '&#41;',
                '&#40;',
                '&#41;'
            ),
            $uString
        ); // '&' => '&#38;'
    }

    /**
     * @ignore
     */
    public static function strip($uString, $uValids)
    {
        $tOutput = "";

        for ($tCount = 0, $tLen = self::length($uString); $tCount < $tLen; $tCount++) {
            $tChar = self::substr($uString, $tCount, 1);
            if (self::strpos($uValids, $tChar) === false) {
                continue;
            }

            $tOutput .= $tChar;
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function squote($uString, $uCover = false)
    {
        // if ($uString === null) {
        //     return 'null';
        // }

        if ($uCover) {
            return '\'' . strtr($uString, array('\\' => '\\\\', '\'' => '\\\'')) . '\'';
        }

        return strtr($uString, array('\\' => '\\\\', '\'' => '\\\''));
    }

    /**
     * @ignore
     */
    public static function dquote($uString, $uCover = false)
    {
        // if ($uString === null) {
        //     return 'null';
        // }

        if ($uCover) {
            return '"' . strtr($uString, array('\\' => '\\\\', '"' => '\\"')) . '"';
        }

        return strtr($uString, array('\\' => '\\\\', '"' => '\\"'));
    }

    /**
     * @ignore
     */
    public static function squoteArray($uArray, $uCover = false)
    {
        static $tSquotes = array('\\' => '\\\\', '\'' => '\\\'');

        $tArray = array();
        foreach ((array)$uArray as $tKey => $tValue) {
            if ($uCover) {
                $tArray[$tKey] = '\'' . strtr($tValue, $tSquotes) . '\'';
                continue;
            }

            $tArray[$tKey] = strtr($tValue, $tSquotes);
        }

        return $tArray;
    }

    /**
     * @ignore
     */
    public static function dquoteArray($uArray, $uCover = false)
    {
        static $tDquotes = array('\\' => '\\\\', '"' => '\\"');

        $tArray = array();
        foreach ((array)$uArray as $tKey => $tValue) {
            if ($uCover) {
                $tArray[$tKey] = '\'' . strtr($tValue, $tDquotes) . '\'';
                continue;
            }

            $tArray[$tKey] = strtr($tValue, $tDquotes);
        }

        return $tArray;
    }

    /**
     * @ignore
     */
    public static function replaceBreaks($uString, $uBreaks = '<br />')
    {
        return strtr($uString, array("\r" => "", "\n" => $uBreaks));
    }

    /**
     * @ignore
     */
    public static function cut($uString, $uLength, $uSuffix = '...')
    {
        if (self::length($uString) <= $uLength) {
            return $uString;
        }

        return rtrim(self::substr($uString, 0, $uLength)) . $uSuffix;
    }

    /**
     * @ignore
     */
    public static function encodeHtml($uString)
    {
        return strtr($uString, array('&' => '&amp;', '"' => '&quot;', '<' => '&lt;', '>' => '&gt;'));
    }

    /**
     * @ignore
     */
    public static function decodeHtml($uString)
    {
        return strtr($uString, array('&amp;' => '&', '&quot;' => '"', '&lt;' => '<', '&gt;' => '>'));
    }

    /**
     * @ignore
     */
    public static function toLower($uString)
    {
        return mb_convert_case($uString, MB_CASE_LOWER);
    }

    /**
     * @ignore
     */
    public static function toUpper($uString)
    {
        return mb_convert_case($uString, MB_CASE_UPPER);
    }

    /**
     * @ignore
     */
    public static function capitalize($uString)
    {
        return mb_convert_case($uString, MB_CASE_TITLE);
    }

    /**
     * @ignore
     */
    public static function length($uString)
    {
        // return mb_strlen($uString);
        return strlen(utf8_decode($uString));
    }

    /**
     * @ignore
     */
    public static function startsWith($uString, $uNeedle)
    {
        // $tLength = mb_strlen($uNeedle);
        $tLength = strlen(utf8_decode($uNeedle));
        if ($tLength === 0) {
            return true;
        }

        return (mb_substr($uString, 0, $tLength) === $uNeedle);
    }

    /**
     * @ignore
     */
    public static function endsWith($uString, $uNeedle)
    {
        // $tLength = mb_strlen($uNeedle);
        $tLength = strlen(utf8_decode($uNeedle));
        if ($tLength === 0) {
            return true;
        }

        return (mb_substr($uString, -$tLength) === $uNeedle);
    }

    /**
     * @ignore
     */
    public static function substr($uString, $uStart, $uLength = null)
    {
        if ($uLength === null) {
            return mb_substr($uString, $uStart);
        }

        return mb_substr($uString, $uStart, $uLength);
    }

    /**
     * @ignore
     */
    public static function strpos($uString, $uNeedle, $uOffset = 0)
    {
        return mb_strpos($uString, $uNeedle, $uOffset);
    }

    /**
     * @ignore
     */
    public static function strstr($uString, $uNeedle, $uBeforeNeedle = false)
    {
        return mb_strstr($uString, $uNeedle, $uBeforeNeedle);
    }

    /**
     * @ignore
     */
    public static function sizeCalc($uSize, $uPrecision = 0)
    {
        static $sSize = ' KMGT';
        for ($tCount = 0; $uSize >= 1024; $uSize /= 1024, $tCount++) {
            ;
        }

        return round($uSize, $uPrecision) . ' ' . $sSize[$tCount] . 'B';
    }

    /**
     * @ignore
     */
    public static function quantityCalc($uSize, $uPrecision = 0)
    {
        static $sSize = ' KMGT';
        for ($tCount = 0; $uSize >= 1000; $uSize /= 1000, $tCount++) {
            ;
        }

        return round($uSize, $uPrecision) . $sSize[$tCount];
    }

    /**
     * @ignore
     */
    public static function timeCalc($uTime)
    {
        if ($uTime >= 60) {
            return number_format($uTime / 60, 2, '.', "") . 'm';
        }

        if ($uTime >= 1) {
            return number_format($uTime, 2, '.', "") . 's';
        }

        return number_format($uTime * 1000, 2, '.', "") . 'ms';
    }

    /**
     * @ignore
     */
    public static function htmlEscape($uString)
    {
        return htmlspecialchars($uString, ENT_COMPAT | ENT_HTML5, mb_internal_encoding());
    }

    /**
     * @ignore
     */
    public static function htmlUnescape($uString)
    {
        return htmlspecialchars_decode($uString, ENT_COMPAT | ENT_HTML5);
    }

    /**
     * @ignore
     */
    private static function readsetGquote($uString, &$uPosition)
    {
        $tInSlash = false;
        $tInQuote = false;
        $tOutput = "";

        for ($tLen = self::length($uString); $uPosition <= $tLen; ++$uPosition) {
            $tChar = self::substr($uString, $uPosition, 1);

            if (($tChar === '\\') && !$tInSlash) {
                $tInSlash = true;
                continue;
            }

            if ($tChar === '"') {
                if (!$tInQuote) {
                    $tInQuote = true;
                    continue;
                }

                if (!$tInSlash) {
                    return $tOutput;
                }
            }
            $tOutput .= $tChar;
            $tInSlash = false;
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function readset($uString)
    {
        $tStart = self::strpos($uString, '[');
        $tOutput = array();
        $tBuffer = "";

        if ($tStart === false) {
            return $tOutput;
        }

        for ($tLen = self::length($uString); $tStart <= $tLen; ++$tStart) {
            $tChar = self::substr($uString, $tStart, 1);

            if ($tChar === ']') {
                $tOutput[] = $tBuffer;

                return $tOutput;
            }

            if ($tChar === ',') {
                $tOutput[] = $tBuffer;
                $tBuffer = "";
                continue;
            }

            if ($tChar === '"') {
                $tBuffer = self::readsetGquote($uString, $tStart);
                continue;
            }
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function parseQueryString($uString, $uParameters = '?&', $uKeys = '=', $uSeparator = null)
    {
        $tParts = explode('#', $uString, 2);

        $tParsed = array(
            '_segments' => array(),
            '_hash' => isset($tParts[1]) ? $tParts[1] : null
        );

        $tStrings = array("", "");
        $tStrIndex = 0;

        $tPos = 0;
        $tLen = self::length($tParts[0]);

        if ($uSeparator !== null) {
            for (; $tPos < $tLen; $tPos++) {
                $tChar = self::substr($tParts[0], $tPos, 1);

                if (self::strpos($uSeparator, $tChar) !== false) {
                    if (self::length($tStrings[1]) > 0) {
                        $tParsed['_segments'][] = $tStrings[1];
                    }

                    $tStrings = array("", null);
                    continue;
                }

                if (self::strpos($uParameters, $tChar) !== false) {
                    break;
                }

                $tStrings[1] .= $tChar;
            }
        }

        if (self::length($tStrings[1]) > 0) {
            if (self::length($tStrings[1]) > 0) {
                $tParsed['_segments'][] = $tStrings[1];
            }

            $tStrings = array("", null);
        }

        for (; $tPos < $tLen; $tPos++) {
            $tChar = self::substr($tParts[0], $tPos, 1);

            if (self::strpos($uParameters, $tChar) !== false) {
                if (self::length($tStrings[0]) > 0 && !array_key_exists($tStrings[0], $tParsed)) {
                    $tParsed[$tStrings[0]] = $tStrings[1];
                    $tStrIndex = 0;
                }

                $tStrings = array("", null);
                continue;
            }

            if (self::strpos($uKeys, $tChar) !== false && $tStrIndex < 1) {
                ++$tStrIndex;
                $tStrings[$tStrIndex] = "";
                continue;
            }

            $tStrings[$tStrIndex] .= $tChar;
        }

        if (self::length($tStrings[0]) > 0) {
            if (self::length($tStrings[0]) > 0 && !array_key_exists($tStrings[0], $tParsed)) {
                $tParsed[$tStrings[0]] = $tStrings[1];
            }
        }

        return $tParsed;
    }

    /**
     * @ignore
     */
    public static function removeAccent($uString)
    {
        static $tAccented = array(
            'À',
            'Á',
            'Â',
            'Ã',
            'Ä',
            'Å',
            'Æ',
            'Ç',
            'È',
            'É',
            'Ê',
            'Ë',
            'Ì',
            'Í',
            'Î',
            'Ï',
            'Ð',
            'Ñ',
            'Ò',
            'Ó',
            'Ô',
            'Õ',
            'Ö',
            'Ø',
            'Ù',
            'Ú',
            'Û',
            'Ü',
            'Ý',
            'ß',
            'à',
            'á',
            'â',
            'ã',
            'ä',
            'å',
            'æ',
            'ç',
            'è',
            'é',
            'ê',
            'ë',
            'ì',
            'í',
            'î',
            'ï',
            'ñ',
            'ò',
            'ó',
            'ô',
            'õ',
            'ö',
            'ø',
            'ù',
            'ú',
            'û',
            'ü',
            'ý',
            'ÿ',
            'Ā',
            'ā',
            'Ă',
            'ă',
            'Ą',
            'ą',
            'Ć',
            'ć',
            'Ĉ',
            'ĉ',
            'Ċ',
            'ċ',
            'Č',
            'č',
            'Ď',
            'ď',
            'Đ',
            'đ',
            'Ē',
            'ē',
            'Ĕ',
            'ĕ',
            'Ė',
            'ė',
            'Ę',
            'ę',
            'Ě',
            'ě',
            'Ĝ',
            'ĝ',
            'Ğ',
            'ğ',
            'Ġ',
            'ġ',
            'Ģ',
            'ģ',
            'Ĥ',
            'ĥ',
            'Ħ',
            'ħ',
            'Ĩ',
            'ĩ',
            'Ī',
            'ī',
            'Ĭ',
            'ĭ',
            'Į',
            'į',
            'İ',
            'ı',
            'Ĳ',
            'ĳ',
            'Ĵ',
            'ĵ',
            'Ķ',
            'ķ',
            'Ĺ',
            'ĺ',
            'Ļ',
            'ļ',
            'Ľ',
            'ľ',
            'Ŀ',
            'ŀ',
            'Ł',
            'ł',
            'Ń',
            'ń',
            'Ņ',
            'ņ',
            'Ň',
            'ň',
            'ŉ',
            'Ō',
            'ō',
            'Ŏ',
            'ŏ',
            'Ő',
            'ő',
            'Œ',
            'œ',
            'Ŕ',
            'ŕ',
            'Ŗ',
            'ŗ',
            'Ř',
            'ř',
            'Ś',
            'ś',
            'Ŝ',
            'ŝ',
            'Ş',
            'ş',
            'Š',
            'š',
            'Ţ',
            'ţ',
            'Ť',
            'ť',
            'Ŧ',
            'ŧ',
            'Ũ',
            'ũ',
            'Ū',
            'ū',
            'Ŭ',
            'ŭ',
            'Ů',
            'ů',
            'Ű',
            'ű',
            'Ų',
            'ų',
            'Ŵ',
            'ŵ',
            'Ŷ',
            'ŷ',
            'Ÿ',
            'Ź',
            'ź',
            'Ż',
            'ż',
            'Ž',
            'ž',
            'ſ',
            'ƒ',
            'Ơ',
            'ơ',
            'Ư',
            'ư',
            'Ǎ',
            'ǎ',
            'Ǐ',
            'ǐ',
            'Ǒ',
            'ǒ',
            'Ǔ',
            'ǔ',
            'Ǖ',
            'ǖ',
            'Ǘ',
            'ǘ',
            'Ǚ',
            'ǚ',
            'Ǜ',
            'ǜ',
            'Ǻ',
            'ǻ',
            'Ǽ',
            'ǽ',
            'Ǿ',
            'ǿ',
            'þ',
            'Þ',
            'ð'
        );
        static $tStraight = array(
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'AE',
            'C',
            'E',
            'E',
            'E',
            'E',
            'I',
            'I',
            'I',
            'I',
            'D',
            'N',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'U',
            'U',
            'U',
            'U',
            'Y',
            's',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'ae',
            'c',
            'e',
            'e',
            'e',
            'e',
            'i',
            'i',
            'i',
            'i',
            'n',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'u',
            'u',
            'u',
            'u',
            'y',
            'y',
            'A',
            'a',
            'A',
            'a',
            'A',
            'a',
            'C',
            'c',
            'C',
            'c',
            'C',
            'c',
            'C',
            'c',
            'D',
            'd',
            'D',
            'd',
            'E',
            'e',
            'E',
            'e',
            'E',
            'e',
            'E',
            'e',
            'E',
            'e',
            'G',
            'g',
            'G',
            'g',
            'G',
            'g',
            'G',
            'g',
            'H',
            'h',
            'H',
            'h',
            'I',
            'i',
            'I',
            'i',
            'I',
            'i',
            'I',
            'i',
            'I',
            'i',
            'IJ',
            'ij',
            'J',
            'j',
            'K',
            'k',
            'L',
            'l',
            'L',
            'l',
            'L',
            'l',
            'L',
            'l',
            'l',
            'l',
            'N',
            'n',
            'N',
            'n',
            'N',
            'n',
            'n',
            'O',
            'o',
            'O',
            'o',
            'O',
            'o',
            'OE',
            'oe',
            'R',
            'r',
            'R',
            'r',
            'R',
            'r',
            'S',
            's',
            'S',
            's',
            'S',
            's',
            'S',
            's',
            'T',
            't',
            'T',
            't',
            'T',
            't',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'W',
            'w',
            'Y',
            'y',
            'Y',
            'Z',
            'z',
            'Z',
            'z',
            'Z',
            'z',
            's',
            'f',
            'O',
            'o',
            'U',
            'u',
            'A',
            'a',
            'I',
            'i',
            'O',
            'o',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'A',
            'a',
            'AE',
            'ae',
            'O',
            'o',
            'b',
            'B',
            'o'
        );

        return str_replace($tAccented, $tStraight, $uString);
    }

    /**
     * @ignore
     */
    public static function removeInvisibles($uString)
    {
        static $tInvisibles = array(
            0,
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            8,
            11,
            12,
            14,
            15,
            16,
            17,
            18,
            19,
            20,
            21,
            22,
            23,
            24,
            25,
            26,
            27,
            28,
            29,
            30,
            31,
            127
        );
        $tOutput = "";

        for ($tCount = 0, $tLen = self::length($uString); $tCount < $tLen; $tCount++) {
            $tChar = self::substr($uString, $tCount, 1);

            if (in_array(ord($tChar), $tInvisibles)) {
                continue;
            }

            $tOutput .= $tChar;
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function slug($uString, $uReplaceWith = '-')
    {
        $uString = self::removeInvisibles($uString);
        $uString = self::removeAccent($uString);
        $uString = strtolower(trim($uString));
        $uString = preg_replace('/[^a-z0-9-]/', $uReplaceWith, $uString);
        $uString = preg_replace('/-+/', $uReplaceWith, $uString);

        return $uString;
    }

    /**
     * @ignore
     */
    public static function toBase($uNumber, $uBase = self::BASECONVERT_BASE62_CHARACTERS)
    {
        $tBaseLength = strlen($uBase);
        $tResult = "";

        do {
            $tIndex = $uNumber % $tBaseLength;
            // if ($tIndex < 0) {
            //    $tIndex += $tBaseLength;
            // }

            $tResult = $uBase[$tIndex] . $tResult;
            $uNumber = ($uNumber - $tIndex) / $tBaseLength;
        } while ($uNumber > 0);

        return $tResult;
    }

    /**
     * @ignore
     */
    public static function fromBase($uNumber, $uBase = self::BASECONVERT_BASE62_CHARACTERS)
    {
        $tBaseLength = strlen($uBase);
        $tResult = strpos($uBase, $uNumber[0]);

        for ($i = 1, $tLength = strlen($uNumber); $i < $tLength; $i++) {
            $tResult = ($tBaseLength * $tResult) + strpos($uBase, $uNumber[$i]);
        }

        return $tResult;
    }

    /**
     * @ignore
     */
    public static function shortenUuid($uString)
    {
        $tParts = array(
            substr($uString, 0, 8),
            substr($uString, 9, 4),
            substr($uString, 14, 4),
            substr($uString, 19, 4),
            substr($uString, 24, 6),
            substr($uString, 30, 6)
        );

        $tShortened = "";
        foreach ($tParts as $tPart) {
            $tEncoded = base_convert($tPart, 16, 10);
            $tShortened .= self::toBase($tEncoded, self::BASECONVERT_URL_CHARACTERS);
        }

        return $tShortened;
    }

    /**
     * @ignore
     */
    public static function unshortenUuid($uString)
    {
        $tParts = array(
            substr($uString, 0, 5),
            substr($uString, 5, 3),
            substr($uString, 8, 3),
            substr($uString, 11, 3),
            substr($uString, 14, 4),
            substr($uString, 18, 4)
        );

        $tUnshortened = "";
        $tIndex = 0;
        foreach ($tParts as $tPart) {
            $tDecoded = self::fromBase($tPart, self::BASECONVERT_URL_CHARACTERS);
            $tUnshortened .= base_convert($tDecoded, 10, 16);
            if ($tIndex++ <= 3) {
                $tUnshortened .= '-';
            }
        }

        return $tUnshortened;
    }

    /**
     * @ignore
     */
    public static function ordinalize($uNumber)
    {
        if (in_array(($uNumber % 100), range(11, 13))) {
            return $uNumber . 'th';
        }

        $tMod = $uNumber % 10;
        if ($tMod === 1) {
            return $uNumber . 'st';
        } elseif ($tMod === 2) {
            return $uNumber . 'nd';
        } elseif ($tMod === 3) {
            return $uNumber . 'rd';
        } else {
            return $uNumber . 'th';
        }
    }

    /**
     * @ignore
     */
    public static function capitalizeEx($uString, $uDelimiter = ' ', $uReplaceDelimiter = null, $uCapitalizeFirst = true)
    {
        $tOutput = "";
        $tCapital = $uCapitalizeFirst;

        for ($tPos = 0, $tLen = self::length($uString); $tPos < $tLen; $tPos++) {
            $tChar = self::substr($uString, $tPos, 1);

            if ($tChar === $uDelimiter) {
                $tCapital = true;
                $tOutput .= ($uReplaceDelimiter !== null) ? $uReplaceDelimiter : $tChar;
                continue;
            }

            if ($tCapital) {
                $tOutput .= self::toUpper($tChar);
                $tCapital = false;
                continue;
            }

            $tOutput .= $tChar;
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function swap(&$uVariable1, &$uVariable2)
    {
        $tTemp = $uVariable1;
        $uVariable1 = $uVariable2;
        $uVariable2 = $tTemp;
    }

    /**
     * @ignore
     *
     * @todo optionally explode by '/', sanitize between
     */
    public static function sanitizeFilename($uFilename, $uRemoveAccent = false, $uRemoveSpaces = false)
    {
        static $sReplaceChars = array(
            '\\' => '-',
            '/' => '-',
            ':' => '-',
            '?' => '-',
            '*' => '-',
            '"' => '-',
            '\'' => '-',
            '<' => '-',
            '>' => '-',
            '|' => '-',
            '.' => '-',
            '+' => '-'
        );

        $tPathInfo = pathinfo($uFilename);
        $tFilename = strtr($tPathInfo['filename'], $sReplaceChars);

        if (isset($tPathInfo['extension'])) {
            $tFilename .= '.' . strtr($tPathInfo['extension'], $sReplaceChars);
        }

        $tFilename = self::removeInvisibles($tFilename);
        if ($uRemoveAccent) {
            $tFilename = self::removeAccent($tFilename);
        }

        if ($uRemoveSpaces) {
            $tFilename = strtr($tFilename, ' ', '_');
        }

        if (isset($tPathInfo['dirname']) && $tPathInfo['dirname'] !== '.') {
            return rtrim(strtr($tPathInfo['dirname'], '\\', '/'), '/') . '/' . $tFilename;
        }

        return $tFilename;
    }

    /**
     * @ignore
     */
    public static function convertLinks($uInput, /* callable */ $uCallback)
    {
        return preg_replace_callback(
            '#((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.]*(\?\S+)?)?)*)#',
            $uCallback,
            $uInput
        );
    }
}
