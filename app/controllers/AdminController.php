<?php

namespace controllers;

use models\Serveur;
use Ubiquity\attributes\items\router\Post;
use Ubiquity\attributes\items\router\Get;

use Ajax\php\ubiquity\JsUtils;
use PHPMV\ProxmoxMaster;
use Ubiquity\controllers\Router;
use Ubiquity\orm\DAO;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\UResponse;

/**
 * Controller AdminController
 * @property JsUtils $jquery
 */
class AdminController extends \controllers\ControllerBase
{
    #[Get(path: "/server/vms", name: "admin.getVms")]
    public function index()
    {
        $vms = $this->api->getVMs();
        $dt = $this->jquery->semantic()->dataTable('vms', \StdClass::class, $vms);
        $dt->setFields(ProxmoxMaster::VM_FIELDS);
        $dt->fieldAsLabel('status', attributes: ['jsCallback' => function ($label, $instance) {
            if ($instance->status == "running") {
                $label->addClass('green');
            }
            if ($instance->status == "stopped") {
                $label->addClass('red');
            }
        }]);
        $this->jquery->renderDefaultView();

        /*$this->loadView("AdminController/index.html");*/
    }

    #[Get(path: "/server/add", name: "admin.addServer")]
    public function addServer()
    {
        $form = $this->jquery->semantic()->htmlForm("addServerForm", [
            $this->jquery->semantic()->htmlInput("server-name", "text", null, "Nom du serveur"),
            $this->jquery->semantic()->htmlInput("server-ip", "text", null, "IP du serveur"),
            $this->jquery->semantic()->htmlInput("server-login", "text", null, "Identifiant"),
            $this->jquery->semantic()->htmlInput("server-pwd", "password", null, "Mot de passe"),
        ]);
        $form->setProperty("method", "POST");
        $form->setProperty("action", Router::path("admin.postAddServer"));
        $form->addExtraFieldRule("server-name", "empty");
        $form->addExtraFieldRule("server-ip", "empty");
        $form->addExtraFieldRule("server-login", "empty");
        $form->addExtraFieldRule("server-pwd", "empty");
/*        $this->jquery->jsonOn('change', '#server-name', Router::path('admin.getServerIpByName', []),
            'post', ['attr' => 'value', "jsCallback" => function ($response) {
                $serverIp = $this->jquery->semantic()->getHtmlComponent("server-ip");
                $serverIp->setProperty("value", $response);
            }]);*/
        $form->addButton("form-submit", "Valider");
        $this->jquery->renderView("AdminController/addServer.html");
    }

    /**
     * @throws \Ubiquity\exceptions\RouterException
     */
    #[Post(path: "/server/add", name: "admin.postAddServer")]
    public function saveServer()
    {
        $values = URequest::getPost();
        $serveur = new Serveur();
        $serveur->setDnsName($values["server-name"]);
        $serveur->setIpAddress($values["server-ip"]);
        $serveur->setLogin($values["server-login"]);
        $serveur->setPassword($values["server-pwd"]);
        DAO::save($serveur);
        $this->forward("controllers\AdminController", "index", null, true, true);
    }


    #[Post(path: "/server/ip/{name}", name: "admin.getServerIpByName")]
    public function getServerIpByNamee($name)
    {
        UResponse::asJSON();
        $name = trim($name);
        echo json_encode(["ip" => empty($name) ? "" : gethostbyname($name)]);
    }

}
