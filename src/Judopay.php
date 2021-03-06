<?php

use Pimple\Container;

/**
 * Main Judopay wrapper
 *
 * Handle initial configuration and build service objects
 *
 * @package      Judopay
 */
class Judopay
{
    const VERSION = '4.0.0';

    /**
     * Pimple DI container
     *
     * @var object Pimple\Container
     **/
    protected $container;

    /**
     * @param array Configuration settings (e.g. [judoId] => '123-456')
     **/
    public function __construct($settings = null)
    {
        // Create new DI container
        $this->container = new Container();

        // Create config object
        $this->container['configuration'] = function ($c) use ($settings) {
            return new \Judopay\Configuration($settings);
        };

        // Create request factory
        $this->container['request'] = $this->container->factory(function ($c) {
            $configuration = $c['configuration'];
            $request = new \Judopay\Request($configuration);
            $request->setClient(new \Judopay\Client());
            $request->setLogger($configuration->get('logger'));

            return $request;
        });
    }

    /**
     * Get an object from the DI container
     *
     * @param string Object name
     * @return object
     **/
    public function get($objName)
    {
        return $this->container[$objName];
    }

    /**
     * Build a new model object
     **/
    public function getModel($modelName)
    {
        // If the model is already defined in the container, just return it
        if (isset($this->container[$modelName])) {
            return $this->get($modelName);
        }

        // Set up the model in the DI container
        $request = $this->get('request');
        $this->container[$modelName] = $this->container->factory(function ($c) use ($modelName, $request) {
            $modelClassName = '\Judopay\Model\\'.ucfirst($modelName);
            $model = new $modelClassName(
                $request
            );

            return $model;
        });

        return $this->get($modelName);
    }
}
