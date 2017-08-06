<?php
# CustomCode

namespace Devtronic\FreshPress\DependencyInjection;

use Symfony\Component\Yaml\Yaml;

/**
 * This is the FreshPress Service Container
 */
class ServiceContainer extends \Devtronic\Injector\ServiceContainer
{
    /**
     * Private Constructor because the ServiceContainer can only be one instance
     *
     * Inject itself as a service with the name service_container
     */
    private function __construct()
    {
        $this->registerService('service_container', function () {
        });
        $this->loadedServices['service_container'] = $this;
    }

    /**
     * Gets the instance of the ServiceContainer
     *
     * @return ServiceContainer The ServiceContainer
     */
    public static function getInstance()
    {
        static $serviceContainer = null;
        if ($serviceContainer === null) {
            $serviceContainer = new ServiceContainer();
        }
        return $serviceContainer;
    }

    /**
     * Load the services from a YAML file
     *
     * Example:
     *
     * services: # Root Node
     *     class: FQCN\MyService                    # Service Class
     *     arguments: ['foo', '@service_container'] # Constructor Arguments
     *
     * @param string $file The YAML file
     * @param string $rootNode The root node
     * @throws \Exception If the file does not exist
     * @throws \Exception If the root node does not exist
     * @throws \Exception If the service class not exist
     */
    public function loadYAML($file, $rootNode = 'services')
    {
        if (!file_exists($file)) {
            throw new \Exception(sprintf('File %s not found', $file));
        }
        $config = Yaml::parse(file_get_contents($file));

        if ($rootNode != '') {
            if (!in_array($rootNode, array_keys($config))) {
                throw new \Exception(sprintf('Root node %s was not found in %s', $rootNode, $file));
            }
            $config = $config[$rootNode];
        }
        $config = (array)$config;

        foreach ($config as $serviceName => $serviceConfig) {
            if (!isset($serviceConfig['class'])) {
                continue;
            } elseif (!class_exists($serviceConfig['class'])) {
                throw new \Exception(sprintf('Service %s not found', $serviceConfig['class']));
            }
            $this->registerService($serviceName, $serviceConfig['class'], $serviceConfig['arguments']);
        }
    }

    /**
     * Load a parameters.yml to pass them to the services
     *
     * Example:
     *
     * parameters: # Root Node
     *      database:
     *          host: 'localhost' # can be accessed with '%database.host%'
     *
     * @param string $file The YAML file
     * @param string $rootNode The root node
     * @throws \Exception If the file does not exist
     * @throws \Exception If the root node does not exist
     * @throws \Exception If the service class not exist
     */
    public function loadParametersYAML($file, $rootNode = 'parameters')
    {
        if (!file_exists($file)) {
            throw new \Exception(sprintf('File %s not found', $file));
        }
        $parameters = Yaml::parse(file_get_contents($file));

        if ($rootNode != '') {
            if (!in_array($rootNode, array_keys($parameters))) {
                throw new \Exception(sprintf('Root node %s was not found in %s', $rootNode, $file));
            }
            $parameters = $parameters[$rootNode];
        }
        $parameters = $this->flattenArray((array)$parameters);

        foreach ($parameters as $name => $value) {
            $this->addParameter($name, $value);
        }
    }

    /**
     * Returns a registered Service
     *
     * @param string $serviceName The name of the Service
     * @return mixed The Service
     */
    public function get($serviceName)
    {
        return $this->loadService($serviceName);
    }

    /**
     * Converts a multidimensional array to ['multi.dimensional.array' => value]
     *
     * @param array $array The Array
     * @param array $result Used internal for keeping the result
     * @param array $currentNodes Used internal to keep the node history
     * @return array The flatten array
     */
    protected function flattenArray($array, $result = [], $currentNodes = [])
    {
        foreach ($array as $k => $v) {
            $currentNodes[] = $k;
            if (is_object($v)) {
                $v = (array)$v;
            }
            if (is_array($v)) {
                $result = $this->flattenArray($v, $result, $currentNodes);
            } else {
                $result[implode('.', $currentNodes)] = $v;
            }
            array_pop($currentNodes);
        }
        return $result;
    }
}
