<?php

namespace Velox\Extra\Breadcrumbs;

class Breadcrumbs {
    var $items = [];

    public function __construct() {
        $this->addItem('<span class="glyphicon glyphicon-home"></span>', '/', false);
    }

    public function addItem($title, $href, $userForBack = true) {
        $this->items[] = [
            'title' => $title,
            'href' => $href,
            'useForBack' => $userForBack
        ];
    }

    public function render() {
        $out = '';
        foreach ($this->items as $a)
            $out .= '<li><a href="' . $a['href'] . '">' . $a['title'] . '</a></li>';

        $back = '';
        $backItems = [];
        foreach ($this->items as $i) {
            if ($i['useForBack'])
                $backItems[] = $i;
        }
        if (count($backItems) > 1) {
            $last = $backItems[count($backItems) - 2];
            $backHref = $last['href'];
            $back = "<a href='$backHref' class='btn btn-transparent btn-xs pull-left mr10' style='margin-top: 7px;'><span class='glyphicon glyphicon-chevron-left'></span> back</a>";
        }
        return "<div>$back<ol class='breadcrumb mb0'>$out</ol></div>";
    }
}
