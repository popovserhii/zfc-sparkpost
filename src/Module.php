<?php
/**
 * Simpler module
 *
 * @category Popov
 * @package Popov_ZfcSparkPost
 * @author Serhii Popov <popow.sergiy@gmail.com>
 * @datetime: 25.07.14 15:04
 */
namespace Popov\ZfcSparkPost;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
