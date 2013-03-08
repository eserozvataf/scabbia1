<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

use Scabbia\Framework;
use Scabbia\Utils;

/**
 * Configuration class which handles all configuration-based operations.
 *
 * @package Scabbia
 * @version 1.1.0
 *
 * @todo _node parsing
 * @todo caching
 */
class Config
{
    /**
     * Default configuration
     */
    public static $default;


    /**
     * Loads the default configuration for the current application.
     *
     * @uses loadFile()
     */
    public static function load()
    {
        $tConfig = array();

        foreach (Utils::glob(Framework::$corepath . 'config/', '*.json', Utils::GLOB_RECURSIVE | Utils::GLOB_FILES) as $tFile) {
            self::loadFile($tConfig, $tFile);
        }

        if (!is_null(Framework::$apppath)) {
            foreach (Utils::glob(Framework::$apppath . 'config/', '*.json', Utils::GLOB_RECURSIVE | Utils::GLOB_FILES) as $tFile) {
                self::loadFile($tConfig, $tFile);
            }
        }

        return $tConfig;
    }

    /**
     * @ignore
     */
    private static function jsonProcessChildrenRecursive(&$uArray, $uNode, &$tNodeStack, $uIsArray = false)
    {
        if (is_object($uNode)) {
            foreach ($uNode as $tKey => $tSubnode) {
                $tNodeName = explode(':', $tKey);

                if (count($tNodeName) >= 2) {
                    switch($tNodeName[1]) {
                        case 'disabled':
                            continue 2;
                            break;
                        case 'development':
                            if (Framework::$development < 1) {
                                continue 2;
                            }
                            break;
                        case 'debug':
                            if (Framework::$development < 2) {
                                continue 2;
                            }
                            break;
                        case 'endpoint':
                            if (Framework::$endpoint != $tNodeName[2]) {
                                continue 2;
                            }
                            break;
                        case 'phpversion':
                            if (!Utils::phpVersion($tNodeName[2])) {
                                continue 2;
                            }
                            break;
                        case 'phpextension':
                            if (!extension_loaded($tNodeName[2])) {
                                continue 2;
                            }
                            break;
                    }
                }

                array_push($tNodeStack, $tNodeName[0]);
                self::jsonProcessChildrenRecursive($uArray, $tSubnode, $tNodeStack);
                array_pop($tNodeStack);
            }
        } else {
            $tNodePath = implode('/', $tNodeStack);

            if ($uIsArray) {
                if (is_array($uNode)) {
                     foreach ($uNode as $tSubnode) {
                         $tNewNodeStack = array();
                         self::jsonProcessChildrenRecursive($uArray[], $tSubnode, $tNewNodeStack, true);
                     }
                } else {
                    $uArray = $uNode;
                }
            } else {
                if (is_array($uNode)) {
                    if (!isset($uArray[$tNodePath])) {
                        $uArray[$tNodePath] = array();
                    }

                    foreach ($uNode as $tSubnode) {
                        $tNewNodeStack = array();
                        self::jsonProcessChildrenRecursive($uArray[$tNodePath][], $tSubnode, $tNewNodeStack, true);
                    }
                } else {
                    $uArray[$tNodePath] = $uNode;
                }
            }
        }
    }

    /**
     * Returns a configuration which is a compilation of a configuration file.
     *
     * @param array  $uConfig   the array which will contain read data
     * @param string $uFile     path of configuration file
     *
     * @return array the configuration
     */
    public static function loadFile(&$uConfig, $uFile)
    {
        $tJsonObject = json_decode(file_get_contents($uFile));

        $tNodeStack = array();
        self::jsonProcessChildrenRecursive($uConfig, $tJsonObject, $tNodeStack);
    }

    /**
     * Gets a value from default configuration.
     *
     * @param string $uKey path of the value
     * @param mixed $uDefault default value
     *
     * @return mixed|null the value
     */
    public static function get($uKey, $uDefault = null)
    {
        if (!array_key_exists($uKey, self::$default)) {
            return $uDefault;
        }

        return self::$default[$uKey];
    }
}