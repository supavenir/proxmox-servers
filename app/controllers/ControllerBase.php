<?php

namespace controllers;

use Ajax\php\ubiquity\JsUtils;
use models\Serveur;
use PHPMV\ProxmoxApi;
use Ubiquity\orm\DAO;
use Ubiquity\utils\http\URequest;
use Ubiquity\controllers\Controller;

/**
 * controllers$ControllerBase
 * @property JsUtils jquery
 */
abstract class ControllerBase extends Controller
{

    protected $headerView = "@activeTheme/main/vHeader.html";

    protected $footerView = "@activeTheme/main/vFooter.html";

    protected $api;

    public function initialize()
    {
        $this->api = new ProxmoxApi('servers1.sts-sio-caen.info', 'sio1a', 'sio1a');
        if (!URequest::isAjax()) {
            $this->loadView($this->headerView);
        }
        $this->makeInitialTemplate();
    }

    public function finalize()
    {
        if (!URequest::isAjax()) {
            $this->loadView($this->footerView);
        }
    }

    public function makeInitialTemplate()
    {
        $div = $this->jquery->semantic()->htmlDivider('burger-menu');
        $menu = $this->jquery->semantic()->htmlAccordionMenu('menu', ["Serveurs", "VMs"]);
        $serveurs = DAO::getAll(Serveur::class);
        $serveurs = array_map(fn($serveur): string => $serveur->getDnsName(), $serveurs);
        $menu->getItem(0)->addContent($this->jquery->semantic()->htmlList("servers", $serveurs));
        $div->addContent($menu);
    }
}

