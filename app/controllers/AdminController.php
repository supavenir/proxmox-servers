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
        $this->jquery->renderView("AdminController/index.html");

        /*$this->loadView("AdminController/index.html");*/
    }

    #[Get(path: "/server/add", name: "admin.addServer")]
    public function addServer()
    {
        $div = $this->jquery->semantic()->htmlDivider("form-container");
        $form=$this->jquery->semantic()->dataForm('addServerForm',new Serveur());
        $form->setFields(["dnsName\n","ipAddress\n",'login','password']); //Select fields to update
        $form->fieldAsInput('dnsName', ["rules" => [Rule::not("", "Veuillez saisir le nom du serveur")]]);
        $form->fieldAsInput('ipAddress', ["rules" => [Rule::not("", "Veuillez saisir l'adresse IP du serveur")]]);
        $form->fieldAsInput('login', ["rules" => [Rule::not("", "Veuillez saisir un identifiant de connexion")]]);
        $form->fieldAsInput('password', ["inputType" => "password", "rules" => ["empty"]]);
        $form->setCaptions(["Nom du serveur", "IP du serveur", "Identifiant", "Mot de passe"]);
        $form->setValidationParams(["on"=>"blur","inline"=>true]);
        $form->setProperty("method", "POST");
        $form->setProperty("action", Router::path("admin.postAddServer"));
/*        $form->addSubmit("submit", "Valider", "blue", Router::path("admin.postAddServer"),
            '#response',['hasLoader'=>'internal']);*/
        $this->jquery->click("#form-submit", '$("#addServerForm").submit()');
        $this->jquery->renderView("AdminController/addServer.html");
    }

    /**
     * @throws \Ubiquity\exceptions\RouterException
     */
    #[Post(path: "/server/add", name: "admin.postAddServer")]
    public function saveServer()
    {
        $serveur = new Serveur();
        URequest::setValuesToObject($serveur);
        try {
            DAO::insert($serveur);
            // Le contexte POST reste après la redirection
            //$this->forward("controllers\AdminController", "index", null, true, true);
            //$this->redirectToRoute("admin.getVms", null, true, true);

            // Le contexte POST reste après la redirection, mais en plus le nombre de serveurs ne bouge pas sur la vue
            // si initialize n'est pas appelé
            //$this->initialize();
            //$this->index();
            header("Location: ../../".Router::path("admin.getVms"));
        } catch (\Exception $e) {

        }
    }


    #[Post(path: "/server/ip/{name}", name: "admin.getServerIpByName")]
    public function getServerIpByNamee($name)
    {
        UResponse::asJSON();
        $name = trim($name);
        echo json_encode(["ip" => empty($name) ? "" : gethostbyname($name)]);
    }

}
