<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia;

use Scabbia\Framework;
use Scabbia\Utils;

/**
 * Global input/output functions which helps framework execution.
 *
 * @package Scabbia
 * @version 1.1.0
 *
 * @todo download garbage collection
 * @todo download caching w/ aging
 * @todo purge (event based garbage collector)
 */
class Io
{
    /**
     * @var int none
     */
    const GLOB_NONE = 0;
    /**
     * @var int recursive
     */
    const GLOB_RECURSIVE = 1;
    /**
     * @var int files
     */
    const GLOB_FILES = 2;
    /**
     * @var int directories
     */
    const GLOB_DIRECTORIES = 4;
    /**
     * @var int just names
     */
    const GLOB_JUSTNAMES = 8;


    /**
     * Reads from a file.
     *
     * @param string    $uPath  the file path
     * @param int       $uFlags io flags
     *
     * @return bool|string the file content
     */
    public static function read($uPath, $uFlags = LOCK_SH)
    {
        $tHandle = fopen($uPath, 'r', false);
        if ($tHandle === false) {
            return false;
        }

        $tLock = flock($tHandle, $uFlags);
        if ($tLock === false) {
            fclose($tHandle);

            return false;
        }

        $tContent = stream_get_contents($tHandle);
        flock($tHandle, LOCK_UN);
        fclose($tHandle);

        return $tContent;
    }

    /**
     * Writes to a file.
     *
     * @param string    $uPath      the file path
     * @param string    $uContent   the file content
     * @param int       $uFlags     io flags
     *
     * @return bool
     */
    public static function write($uPath, $uContent, $uFlags = LOCK_EX)
    {
        $tHandle = fopen(
            $uPath,
            ($uFlags & FILE_APPEND) > 0 ? 'a' : 'w',
            false
        );
        if ($tHandle === false) {
            return false;
        }

        if (flock($tHandle, $uFlags) === false) {
            fclose($tHandle);

            return false;
        }

        fwrite($tHandle, $uContent);
        fflush($tHandle);
        flock($tHandle, LOCK_UN);
        fclose($tHandle);

        return true;
    }

    /**
     * Reads from a serialized file.
     *
     * @param string        $uPath      the file path
     * @param string|null   $uKeyphase  the key
     *
     * @return bool|mixed   the unserialized object
     */
    public static function readSerialize($uPath, $uKeyphase = null)
    {
        $tContent = self::read($uPath);

        //! ambiguous return value
        if ($tContent === false) {
            return false;
        }

        if ($uKeyphase !== null && strlen($uKeyphase) > 0) {
            $tContent = Utils::decrypt($tContent, $uKeyphase);
        }

        return unserialize($tContent);
    }

    /**
     * Serializes an object into a file.
     *
     * @param string        $uPath      the file path
     * @param string        $uContent   the file content
     * @param string|null   $uKeyphase  the key
     *
     * @return bool
     */
    public static function writeSerialize($uPath, $uContent, $uKeyphase = null)
    {
        $tContent = serialize($uContent);

        if ($uKeyphase !== null && strlen($uKeyphase) > 0) {
            $tContent = Utils::encrypt($tContent, $uKeyphase);
        }

        return self::write($uPath, $tContent);
    }

    /**
     * Updates a modification time of a file.
     *
     * @param string    $uPath      the file path
     *
     * @return bool
     */
    public static function touch($uPath)
    {
        return touch($uPath);
    }

    /**
     * Deletes a file.
     *
     * @param string    $uPath      the file path
     *
     * @return bool
     */
    public static function destroy($uPath)
    {
        if (file_exists($uPath)) {
            return unlink($uPath);
        }

        return false;
    }

    /**
     * Translates given framework-relative path to physical path.
     *
     * @param string    $uPath          the framework-relative path
     * @param bool      $uCreateFolder  creates path if does not exist
     *
     * @throws \Exception
     * @return string translated physical path
     */
    public static function translatePath($uPath, $uCreateFolder = false)
    {
        if (strncmp($uPath, '{base}', 6) === 0) {
            $uPath = Framework::$basepath . substr($uPath, 6);
        } elseif (strncmp($uPath, '{core}', 6) === 0) {
            $uPath = Framework::$corepath . substr($uPath, 6);
        } elseif (strncmp($uPath, '{vendor}', 8) === 0) {
            $uPath = Framework::$vendorpath . substr($uPath, 8);
        } elseif (Framework::$application !== null) {
            if (strncmp($uPath, '{app}', 5) === 0) {
                $uPath = Framework::$application->path . substr($uPath, 5);
            } elseif (strncmp($uPath, '{writable}', 10) === 0) {
                $uPath = Framework::$application->path . 'writable/' . substr($uPath, 10);
            }
        }

        if ($uCreateFolder) {
            $tPathDirectory = pathinfo($uPath, PATHINFO_DIRNAME);

            if (!is_dir($tPathDirectory)) {
                if (Framework::$readonly) {
                    throw new \Exception($tPathDirectory . ' does not exists.');
                }

                mkdir($tPathDirectory, 0777, true);
            }
        }

        return $uPath;
    }

    /**
     * Converts a namespace to proper path.
     *
     * @param string $uName  the name
     *
     * @return string relative path
     */
    public static function namespacePath($uName)
    {
        /*
        $tExploded = explode('/', trim(strtr($uName, '\\', '/'), '/'));

        $tName = "";
        foreach ($tExploded as $tExplodedPart) {
            if (strlen($tName) > 0) {
                $tName .= '/';
            }
            $tName .= $tExplodedPart;
        }

        return $tName;
        */

        return trim(strtr($uName, '\\', '/'), '/');
    }

    /**
     * Extracts a path from different path.
     *
     * @param string    $uPath      the full path
     * @param string    $uBasePath  path to extract
     *
     * @return string relative path
     */
    public static function extractPath($uPath, $uBasePath = null)
    {
        $uPath = strtr($uPath, '\\', '/');

        if ($uBasePath === null) {
            $uBasePath = Framework::$basepath;
        }

        $tLen = strlen($uBasePath);
        if (strncmp($uPath, $uBasePath, $tLen) === 0) {
            return substr($uPath, $tLen);
        }

        return $uPath;
    }

    /**
     * Determines the file is if readable and not expired.
     *
     * @param string    $uFile  the relative path
     * @param int       $uTtl   the time to live period in seconds
     *
     * @return bool the result
     */
    public static function isReadable($uFile, $uTtl = -1)
    {
        if (!file_exists($uFile)) {
            return false;
        }

        return ($uTtl < 0 || (time() - filemtime($uFile) <= $uTtl));
    }

    /**
     * Determines the file is if readable and newer than given timestamp.
     *
     * @param string    $uFile          the relative path
     * @param int       $uLastModified  the time to live period in seconds
     *
     * @return bool the result
     */
    public static function isReadableAndNewer($uFile, $uLastModified)
    {
        return (file_exists($uFile) && filemtime($uFile) >= $uLastModified);
    }

    /**
     * Reads the contents from cache file as long as it is not expired.
     * If the file is expired, invokes callback method and caches output.
     *
     * @param string        $uFile      the relative path
     * @param int           $uTtl       the time to live period in seconds
     * @param callback|null $uCallback  the callback method
     *
     * @return mixed the result
     */
    public static function readFromCache($uFile, $uTtl = -1, /* callable */ $uCallback = null)
    {
        if (self::isReadable($uFile, $uTtl)) {
            return self::readSerialize($uFile);
        }

        if ($uCallback === null) {
            return false;
        }

        $tResult = call_user_func($uCallback);
        self::writeSerialize($uFile, $tResult);

        return $tResult;
    }

    /**
     * Garbage collects the given path
     *
     * @param string    $uPath  path
     * @param int       $uTtl   age
     */
    public static function garbageCollect($uPath, $uTtl = -1)
    {
        $tDirectory = new \DirectoryIterator($uPath);

        clearstatcache();
        foreach ($tDirectory as $tFile) {
            if (!$tFile->isFile()) {
                continue;
            }

            if ($uTtl !== -1 && (time() - $tFile->getMTime()) < $uTtl) {
                continue;
            }

            self::destroy($tFile->getPathname());
        }
    }

    /**
     * Downloads given file into framework's download directory.
     *
     * @param string    $uFile  filename in destination
     * @param string    $uUrl   url of source
     *
     * @return bool whether the file is downloaded or not
     */
    public static function downloadFile($uFile, $uUrl)
    {
        $tUrlHandle = fopen($uUrl, 'rb', false);
        if ($tUrlHandle === false) {
            return false;
        }

        $tHandle = fopen(self::translatePath('{writable}downloaded/' . $uFile, true), 'wb', false);
        if ($tHandle === false) {
            fclose($tUrlHandle);

            return false;
        }

        if (flock($tHandle, LOCK_EX) === false) {
            fclose($tHandle);
            fclose($tUrlHandle);

            return false;
        }

        stream_copy_to_stream($tUrlHandle, $tHandle);
        fflush($tHandle);
        flock($tHandle, LOCK_UN);
        fclose($tHandle);

        fclose($tUrlHandle);

        return true;
    }

    /**
     * Returns a php file source to view.
     *
     * @param string      $uPath            the path will be searched
     * @param string|null $uFilter          the pattern
     * @param int         $uOptions         the flags
     * @param string      $uRecursivePath   the path will be concatenated (recursive)
     * @param array       $uArray           the results array (recursive)
     *
     * @return array|bool the search results
     */
    public static function glob(
        $uPath,
        $uFilter = null,
        $uOptions = self::GLOB_FILES,
        $uRecursivePath = "",
        array &$uArray = array()
    ) {
        $tPath = rtrim(strtr($uPath, '\\', '/'), '/') . '/';
        $tRecursivePath = $tPath . $uRecursivePath;

        // if (file_exists($tRecursivePath)) {
        try {
            $tDir = new \DirectoryIterator($tRecursivePath);

            foreach ($tDir as $tFile) {
                $tFileName = $tFile->getFilename();

                if ($tFileName[0] === '.') { // $tFile->isDot()
                    continue;
                }

                if ($tFile->isDir()) {
                    $tDirectory = $uRecursivePath . $tFileName . '/';

                    if (($uOptions & self::GLOB_DIRECTORIES) > 0) {
                        $uArray[] = (($uOptions & self::GLOB_JUSTNAMES) > 0) ? $tDirectory : $tPath . $tDirectory;
                    }

                    if (($uOptions & self::GLOB_RECURSIVE) > 0) {
                        self::glob(
                            $tPath,
                            $uFilter,
                            $uOptions,
                            $tDirectory,
                            $uArray
                        );
                    }

                    continue;
                }

                if (($uOptions & self::GLOB_FILES) > 0 && $tFile->isFile()) {
                    if ($uFilter === null || fnmatch($uFilter, $tFileName)) {
                        $uArray[] = (($uOptions & self::GLOB_JUSTNAMES) > 0) ?
                            $uRecursivePath . $tFileName :
                            $tRecursivePath . $tFileName;
                    }

                    continue;
                }
            }

            return $uArray;
        } catch (\Exception $tException) {
            // echo $tException->getMessage();
        }
        // }

        $uArray = false;

        return $uArray;
    }

    /**
     * Gets the last modification date from the list of files
     *
     * @param array|string $uFiles list of files
     *
     * @return int last modification
     */
    public static function getLastModified($uFiles)
    {
        $tLastModified = -1;

        foreach ((array)$uFiles as $tFile) {
            $tFileMod = filemtime($tFile);

            if ($tLastModified < $tFileMod) {
                $tLastModified = $tFileMod;
            }
        }

        return $tLastModified;
    }
}
