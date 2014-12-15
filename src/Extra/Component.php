<?php

namespace Velox\Extra;

use Velox\Extra\Breadcrumbs\Breadcrumbs;
use Velox\Extra\FlashBag\FlashBag;
use Velox\Framework\Kernel\BaseComponent;
use Velox\Framework\Registry\Service;

class Component extends BaseComponent {
    public function getServices() {
        return [
            'Velox.Extra.FlashBag' => new Service(function() {
                    return new FlashBag();
                }),
            'Velox.Extra.Breadcrumbs' => new Service(function() {
                    return new Breadcrumbs();
                })
        ];
    }

    public function getRoutes() {
        return [];
    }

    public function getEventListeners() {
        return [];
    }
}
