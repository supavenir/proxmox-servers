<?php

namespace controllers;

use Ajax\bootstrap\html\HtmlLink;
use Ajax\php\ubiquity\JsUtils;
use Ajax\Semantic;
use Ajax\semantic\html\elements\HtmlHeader;
use models\Serveur;
use PHPMV\ProxmoxApi;
use Ubiquity\controllers\Controller;
use Ubiquity\controllers\Router;
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
        $this->api = new ProxmoxApi('servers2.sts-sio-caen.info', 'sio2a', 'sio2a');
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
        $menu = $this->semantic->htmlAccordion('menu');
        $serveurs = DAO::getAll(Serveur::class);
        //$serveurs = array_map(fn($serveur): string => $serveur->getDnsName(), $serveurs);
        $vms = $this->api->getVms();
        $vms = array_map(fn($vm): string => $vm['name'], $vms);
        $menu->addItem(["Serveurs - " . count($serveurs), []/*$this->semantic->htmlList("servers", $serveurs*/]);

        foreach ($serveurs as $serveur){
            $div = $this->semantic->htmlCard("server-card-".$serveur->getId());
            $div->addItemContent([$this->semantic->htmlHeader("server-title-".$serveur->getId(), 4, $serveur->getDnsName())]);
            $div->addExtraContent("coucou");
            $div->asLink("https://google.com");
/*            $div->_ajaxOn("get", "click", Router::url("serveur.getResumeById", ["id" => $serveur->getId()]),
                "body", ["value" => "Valider", 'hasLoader' => 'internal']);*/
            $menu->getItem(0)->addContent($div);
        }

        $menu->addItem(["VMs - " . count($vms), $this->semantic->htmlList("servers", $vms)]);
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

