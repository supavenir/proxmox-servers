<?php

namespace controllers;

use Ajax\semantic\components\validation\Rule;
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
        $div = $this->jquery->semantic()->htmlDivider("form-container");
        $form=$this->jquery->semantic()->dataForm('addServerForm',new Serveur());
        $form->setFields(["name\n","ip\n",'login','password']); //Select fields to update
        $form->fieldAsInput(0, ["rules" => [Rule::not("", "Veuillez saisir le nom du serveur")]]);
        $form->fieldAsInput(1, ["rules" => [Rule::not("", "Veuillez saisir l'adresse IP du serveur")]]);
        $form->fieldAsInput(2, ["rules" => [Rule::not("", "Veuillez saisir un identifiant de connexion")]]);
        $form->fieldAsInput(3, ["inputType" => "password", "rules" => ["empty"]]);
        $form->setCaptions(["Nom du serveur", "IP du serveur", "Identifiant", "Mot de passe"]);
        $form->setValidationParams(["on"=>"blur","inline"=>true]);
        $form->setProperty("method", "POST");
        $form->setProperty("action", Router::path("admin.postAddServer"));
        $this->jquery->click("#form-submit", '$("#addServerForm").submit()');
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
        $serveur->setDnsName($values["name"]);
        $serveur->setIpAddress($values["ip"]);
        $serveur->setLogin($values["login"]);
        $serveur->setPassword($values["password"]);
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
