<?php

namespace controllers;

use Ajax\php\ubiquity\JsUtils;
use Ubiquity\attributes\items\router\Get;

/**
 * Controller AdminController
 * @property JsUtils $jquery
 */
class AdminController extends \controllers\ControllerBase
{
    #[Get(path: "/administration/", name: "admin.getVms")]
    public function index()
    {
        $this->loadView("AdminController/index.html");
    }
}
