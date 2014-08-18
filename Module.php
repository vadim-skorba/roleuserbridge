<?php

namespace RoleUserBridge;

use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

use RoleUserBridge\Mapper\RoleMapper;

use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use ZfcUser\Mapper;

class Module implements AutoloaderProviderInterface,
    ConfigProviderInterface,
    ServiceProviderInterface
{

    public function getAutoloaderConfig()
    {
        return array(
                'Zend\Loader\StandardAutoloader' => array(
                        'namespaces' => array(
                                __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                        ),
                ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'zfcuser_user_service' => 'RoleUserBridge\Service\User',
            ),
            'factories' => array(
                'user_role_mapper' => function ($sm) {
                    $options = $sm->get('zfcuser_module_options');
                    $crypto  = new Bcrypt;
                    $crypto->setCost($options->getPasswordCost());
                    $config = $sm->get('config');
                    $mapper = new RoleMapper($config);
                    $mapper->setDbAdapter($sm->get('zfcuser_zend_db_adapter'));
                    $entityClass = $options->getUserEntityClass();
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new \ZfcUser\Mapper\UserHydrator($crypto));
                    return $mapper;
                },
            )
        );
    }
}
