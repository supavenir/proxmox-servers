<?php

namespace controllers;

use Ajax\php\ubiquity\JsUtils;
use Ajax\Semantic;
use models\Serveur;
use PHPMV\ProxmoxApi;
use Ubiquity\controllers\Controller;
use Ubiquity\orm\DAO;
use Ubiquity\utils\http\URequest;

/**
 * controllers$ControllerBase
 * @property JsUtils jquery
 */
abstract class ControllerBase extends Controller
{

    protected $headerView = "@activeTheme/main/vHeader.html";

    protected $footerView = "@activeTheme/main/vFooter.html";

    /**
     * @var ProxmoxApi
     */
    protected $api;

    /**
     * @var Semantic
     */
    protected $semantic;

    public function initialize()
    {
        $this->api = new ProxmoxApi('servers1.sts-sio-caen.info', 'sio1a', 'sio1a');
        $this->semantic = $this->jquery->semantic();
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
        $menu = $this->jquery->semantic()->htmlAccordion('menu');
        $serveurs = DAO::getAll(Serveur::class);
        $serveurs = array_map(fn($serveur): string => $serveur->getDnsName(), $serveurs);
        $vms = $this->api->getVms();
        $vms = array_map(fn($vm): string => $vm['name'], $vms);
        $menu->addItem(["Serveurs - " . count($serveurs), $this->jquery->semantic()->htmlList("servers", $serveurs)]);
        $menu->addItem(["VMs - " . count($vms), $this->jquery->semantic()->htmlList("servers", $vms)]);
        $menu->getItem(0)->setActive(true);
        $menu->setStyled();
    }

    public function getVmStatusByName($name){
        $vms = $this->api->getVMs();
        $result = null;
        for($i=0;$i<count($vms);$i++){
            if($vms[$i]["name"] === $name){
                $result = $vms[$i];
                break;
            }
        }
        return $result;
    }
}

