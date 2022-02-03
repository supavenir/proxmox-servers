<?php

namespace controllers;

use Ajax\php\ubiquity\JsUtils;
use PHPMV\ProxmoxApi;
use PHPMV\ProxmoxMaster;

/**
 * Controller AdminController
 * @property JsUtils $jquery
 */
class AdminController extends \controllers\ControllerBase
{

    public function index()
    {
        $api = new ProxmoxApi('servers1.sts-sio-caen.info', 'sio1a', 'sio1a');
        $vms = $api->getVMs();
        $dt = $this->jquery->semantic()->dataTable('vms', \StdClass::class, $vms);
        $dt->setFields(ProxmoxMaster::VM_FIELDS);
        $dt->fieldAsLabel('status', ['jsCallback' => function ($lbl, $instance) {
            if ($instance->status === 'running') {
                $lbl->addClass('green');
            }
        }]);
        $this->makeInitialTemplate();
        $this->jquery->renderDefaultView();

        /*$this->loadView("AdminController/index.html");*/
    }

    public function makeInitialTemplate(){
        $div = $this->jquery->semantic()->htmlDivider('burger-menu');
        $menu = $this->jquery->semantic()->htmlAccordionMenu('menu', ["Serveurs", "VMs"]);
        $div->addContent($menu);
    }
}
