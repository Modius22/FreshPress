<?php

namespace Devtronic\FreshPress\Core\Util;

use Symfony\Component\Yaml\Yaml;

class ConfigMapper
{
    private $constantMap = [
        'core.debug' => 'WP_DEBUG',
        'core.debug_display' => 'WP_DEBUG_DISPLAY',
        'core.debug_log' => 'WP_DEBUG_LOG',
        'core.debug_scripts' => 'WP_SCRIPT_DEBUG',
        'core.auto_update_core' => 'WP_AUTO_UPDATE_CORE',
        'core.allow_multisite' => 'WP_ALLOW_MULTISITE',
        'core.allow_repair' => 'WP_ALLOW_REPAIRE',
        'core.proxy.host' => 'WP_PROXY_HOST',
        'core.proxy.port' => 'WP_PROXY_PORT',
        'core.proxy.user' => 'WP_PROXY_USERNAME',
        'core.proxy.pass' => 'WP_PROXY_PASSWORD',
        'core.proxy.bypass_hosts' => 'WP_PROXY_BYBASS_HOSTS',
        'core.proxy.block_external' => 'WP_HTTP_BLOCK_EXTERNAL',
        'core.filesystem_method' => 'FS_METHOD',
        'core.directories.languages' => 'WP_LANG_DIR',
        'core.directories.plugins' => 'WP_PLUGIN_DIR',
        'core.plugin_url' => 'WP_PLUGIN_URL',
        'multisite.no_blog_redirect' => 'NOBLOGREDIRECT',
        'multisite.subdomain_install' => 'SUBDOMAIN_INSTALL',
        'multisite.directories.plugins' => 'WPMU_PLUGIN_DIR',
        'multisite.plugin_url' => 'WPMU_PLUGIN_URL',
    ];

    public function mapConfiguration($yamlFile)
    {
        $data = Yaml::parse(file_get_contents($yamlFile));

        $flatten = $this->flattenArray($data);
        foreach ($flatten as $parameter => $value) {
            if ($value === null) {
                continue;
            }
            if (isset($this->constantMap[$parameter]) && !defined($this->constantMap[$parameter])){
                if ($value === 'null'){
                    $value = null;
                }
                define($this->constantMap[$parameter], $value);
            }
        }
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