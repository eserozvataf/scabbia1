<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Mvc;

use Scabbia\Config;
use Scabbia\Delegate;
use Scabbia\Extensions\Datasources\Datasources;
use Scabbia\Extensions\Logger\Logger;
use Scabbia\Extensions\Mvc\Controllers;
use Scabbia\Extensions\Views\Views;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Mvc Extension: ControllerBase Class
 *
 * @package Scabbia
 * @subpackage Mvc
 * @version 1.1.0
 */
class ControllerBase implements LoggerAwareInterface
{
    /**
     * @ignore
     */
    public $childControllers = array();
    /**
     * @ignore
     */
    public $defaultAction = 'index';
    /**
     * @ignore
     */
    public $view = null;
    /**
     * @ignore
     */
    public $prerender;
    /**
     * @ignore
     */
    public $postrender;
    /**
     * @ignore
     */
    public $vars = array();
    /**
     * @ignore
     */
    public $route = null;
    /**
     * @ignore
     */
    public $db;
    /**
     * @ignore
     */
    public $logger;


    /**
     * @ignore
     */
    public function __construct()
    {
        $this->db = Datasources::get(); // default datasource to member 'db'
        $this->logger = Logger::getInstance();

        $this->prerender = new Delegate();
        $this->postrender = new Delegate();
    }

    /**
     * @ignore
     */
    public function setLogger(LoggerInterface $uLogger)
    {
        $this->logger = $uLogger;
    }

    /**
     * @ignore
     */
    public function render($uAction, array $uParams, array $uInput)
    {
        $tActionName = $uAction; // strtr($uAction, '/', '_');
        if (is_null($tActionName) || strlen($tActionName) <= 0) {
            $tActionName = $this->defaultAction;
        }

        if (isset($this->childControllers[$tActionName])) {
            if (count($uParams) > 0) {
                $tSubaction = array_shift($uParams);
            } else {
                $tSubaction = null;
            }

            $tInstance = new $this->childControllers[$tActionName] ();
            return $tInstance->render($tSubaction, $uParams, $uInput);
        }

        $tMe = new \ReflectionClass($this);

        while (true) {
            $tMethod = $uInput['methodext'] . '_' . $tActionName;
            if ($tMe->hasMethod($tMethod) && $tMe->getMethod($tMethod)->isPublic()) {
                break;
            }

            // fallback
            $tMethod = $uInput['method'] . '_' . $tActionName;
            if ($tMe->hasMethod($tMethod) && $tMe->getMethod($tMethod)->isPublic()) {
                break;
            }

            // fallback 2
            $tMethod = $tActionName;
            if ($tMe->hasMethod($tMethod) && $tMe->getMethod($tMethod)->isPublic()) {
                break;
            }

            // fallback 3
            $tMethod = 'otherwise';
            if ($tMe->hasMethod($tMethod) && $tMe->getMethod($tMethod)->isPublic()) {
                break;
            }

            return false;
        }

        array_push(Controllers::$stack, $this);

        $this->route = array(
            'controller' => get_class($this),
            'action' => $tActionName,
            'params' => $uParams,
            'query' => isset($uInput['query']) ? $uInput['query'] : ''
        );

        if (($tPos = strrpos($this->route['controller'], '\\')) !== false) {
            $this->route['controller'] = substr($this->route['controller'], $tPos + 1);
        }

        $this->view = $this->route['controller'] .
            '/' .
            $this->route['action'] .
            '.' .
            Config::get('mvc/view/defaultViewExtension', 'php');

        $this->prerender->invoke();

        $tReturn = call_user_func_array(array(&$this, $tMethod), $uParams);

        $this->postrender->invoke();
        array_pop(Controllers::$stack);

        return $tReturn;
    }

    /**
     * @ignore
     */
    public function addChildController($uAction, $uClass)
    {
        // echo $uAction . " => " . $uClass . '<br />';
        $this->childControllers[$uAction] = $uClass;
    }

    /**
     * @ignore
     */
    public function export()
    {
    }

    /**
     * @ignore
     */
    public function get($uKey)
    {
        return $this->vars[$uKey];
    }

    /**
     * @ignore
     */
    public function set($uKey, $uValue)
    {
        $this->vars[$uKey] = $uValue;
    }

    /**
     * @ignore
     */
    public function setRef($uKey, &$uValue)
    {
        $this->vars[$uKey] = $uValue;
    }

    /**
     * @ignore
     */
    public function setRange(array $uArray)
    {
        foreach ($uArray as $tKey => $tValue) {
            $this->vars[$tKey] = $tValue;
        }
    }

    /**
     * @ignore
     */
    public function remove($uKey)
    {
        unset($this->vars[$uKey]);
    }

    /**
     * @ignore
     */
    public function loadDatasource($uDatasourceName, $uMemberName = null)
    {
        $uArgs = func_get_args();

        if (is_null($uMemberName)) {
            $uMemberName = $uDatasourceName;
        }

        $this->{$uMemberName} = call_user_func_array('Scabbia\\Extensions\\Mvc\\Controllers::loadDatasource', $uArgs);
    }

    /**
     * @ignore
     */
    public function load($uModelClass, $uMemberName = null)
    {
        $uArgs = func_get_args();

        if (is_null($uMemberName)) {
            if (($tPos = strrpos($uModelClass, '\\')) !== false) {
                $uMemberName = substr($uModelClass, $tPos + 1);
            } else {
                $uMemberName = $uModelClass;
            }
        }

        $this->{$uMemberName} = call_user_func_array('Scabbia\\Extensions\\Mvc\\Controllers::load', $uArgs);
    }

    /**
     * @ignore
     */
    public function view($uView = null, $uModel = null)
    {
        Views::viewFile(
            '{app}views/' . (!is_null($uView) ? $uView : $this->view),
            !is_null($uModel) ? $uModel : $this->vars
        );
    }

    /**
     * @ignore
     */
    public function viewFile($uView = null, $uModel = null)
    {
        Views::viewFile(
            !is_null($uView) ? $uView : $this->view,
            !is_null($uModel) ? $uModel : $this->vars
        );
    }

    /**
     * @ignore
     */
    public function json($uModel = null)
    {
        Views::json(
            !is_null($uModel) ? $uModel : $this->vars
        );
    }

    /**
     * @ignore
     */
    public function xml($uModel = null)
    {
        Views::xml(
            !is_null($uModel) ? $uModel : $this->vars
        );
    }

    /**
     * @ignore
     */
    public function end()
    {
        $uArgs = func_get_args();
        call_user_func_array('Scabbia\\Framework::end', $uArgs);
    }
}
