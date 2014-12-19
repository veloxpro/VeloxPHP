<?php

namespace Velox\Security;

use Velox\Framework\Event\Event;
use Velox\Framework\Event\EventListener;
use Velox\Framework\Kernel\BaseComponent;
use Velox\Framework\Registry\Registry;
use Velox\Framework\Registry\Service;
use Velox\Security\Authentication\AuthenticationManager;
use Velox\Security\Authentication\UserProvider\FileUserProvider;
use Velox\Security\Authorization\AuthorizationManager;

class Component extends BaseComponent {
    public function getServices() {
        return array(
            'Velox.Security.AuthenticationManager' => new Service(function() {
                    $config = array(
                        'userEntityClass' => 'Velox\\Security\\Authentication\\BaseUser',
                        'userProvider' => array(
                            'Velox\\Security\\Authentication\\UserProvider\\FileUserProvider',
                            array('path' => 'app/config/user.config.php')
                        ),
                        'usernameFormField' => '_username',
                        'passwordFormField' => '_password',
                        'rememberFormField' => '_remember',
                        'rememberExpire' => 360 * 24 * 60 * 60,
                        'salt' => 'T(!AvUFj&,>k{N>4X/{(oDX@+y&gi;?4wo70vN3_1k@/EtV*yY>2Z#DaPEk01 J4',
                    );
                    $configPath = 'app/config/security.config.php';
                    if (is_readable($configPath)) {
                        $c = include $configPath;
                        if (is_array($c) && isset($c['Authentication']) && is_array($c['Authentication']))
                            $config = array_merge($config, $c['Authentication']);
                    }

                    $authenticationManager = new AuthenticationManager();
                    $authenticationManager->setUserEntityClass($config['userEntityClass']);
                    $authenticationManager->setUsernameFormField($config['usernameFormField']);
                    $authenticationManager->setPasswordFormField($config['passwordFormField']);
                    $authenticationManager->setRememberFormField($config['rememberFormField']);
                    $authenticationManager->setRememberExpire($config['rememberExpire']);
                    $authenticationManager->setSalt($config['salt']);

                    $userProvider = $config['userProvider'][0];
                    $userProvider = new $userProvider($authenticationManager->getUserEntityClass());
                    foreach ($config['userProvider'][1] as $name => $value)
                        $userProvider->{'set'.ucfirst($name)}($value);
                    $authenticationManager->setUserProvider($userProvider);

                    $authenticationManager->handleRequest(Registry::get('Velox.Http.Request'));
                    return $authenticationManager;
                }),
            'Velox.Security' => new Service(function() {
                    return new Security();
                }),
        );
    }

    public function getRoutes() {
        return array();
    }

    public function getEventListeners() {
        return array(
            new EventListener(array('Velox.Kernel.Launch'), function(Event $event) {
                //Registry::get('Velox.Security.AuthenticationManager')->init();

                $security = Registry::get('Velox.Security');
                $security->execute();
            }),
        );
    }
}
