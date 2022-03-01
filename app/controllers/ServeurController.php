<?php

namespace controllers;

use Ajax\semantic\components\validation\Rule;
use Ajax\semantic\html\elements\HtmlButton;
use models\Serveur;
use Ubiquity\attributes\items\router\Get;
use Ubiquity\attributes\items\router\Post;
use Ubiquity\controllers\Router;
use Ubiquity\orm\DAO;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\UResponse;

/**
 * Controller ServeurController
 */
class ServeurController extends \controllers\ControllerBase
{

    #[Get(path: "server", name: "serveur.getAll")]
    public function index()
    {
        $dt = $this->semantic->dataTable("servers", \StdClass::class, DAO::getAll(Serveur::class));
        $dt->setIdentifierFunction('getId');
        $dt->setFields(["dnsName", "ipAddress", "login", "VMs"]);
        $dt->setCaptions(["Nom du serveur", "Adresse IP", "Identifiant", "Nombre de VMs", "Actions"]);
        $dt->addDisplayButton(true, [],/* function (HtmlButton $object, Serveur $instance) {
            $object->setIdentifier("server-" . $instance->getId());
            $this->jquery->_add_event("#server-" . $instance->getId(), "
                window.location.href = '" . Router::url("serveur.getResumeById", ["id" => $instance->getId()]) . "'
            ", "click");
        }*/);
        $this->jquery->getOnClick('._display',Router::url("serveur.getResumeById",['']),'body',['attr'=>'data-ajax']);
        $dt->setStriped();
        $dt->setCelled();
        $this->jquery->renderView("ServeurController/index.html");
        /*$this->loadView("ServeurController/index.html");*/
    }

    #[Get(path: "server/add", name: "serveur.addServer")]
    public function addServer()
    {
        $form = $this->semantic->dataForm('addServerForm', new Serveur());
        $form->setFields(["dnsName\n", "description\n", "ipAddress\n", "login", "password\n", "btSubmit"]); //Select fields to update
        $form->fieldAsInput(0, ["rules" => [Rule::not("", "Veuillez saisir le nom du serveur")]]);
        $form->fieldAsInput(1, ["rules" => [Rule::not("", "Veuillez saisir une description du serveur")]]);
        $form->fieldAsInput(2, ["rules" => [
            Rule::not("", "Veuillez saisir l'adresse IP du serveur"),
            Rule::regExp('^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$', "Le format de l'IP est invalide")
        ]]);
        $form->fieldAsInput(3, ["rules" => [Rule::not("", "Veuillez saisir un identifiant de connexion")]]);
        $form->fieldAsInput(4, ["inputType" => "password", "rules" => [["empty", "Veuillez saisir un mot de passe de connexion"]]]);
        $form->setCaptions(["Nom du serveur", "Description du serveur", "IP du serveur", "Identifiant", "Mot de passe"]);
        $form->setValidationParams(["on" => "blur", "inline" => true]);
        $form->setProperty("method", "POST");
        $form->setProperty("action", Router::path("serveur.saveServer"));
        $form->fieldAsSubmit("btSubmit", "blue", Router::path("serveur.saveServer"),
            'body', ["value" => "Valider", 'hasLoader' => 'internal']);
        $this->jquery->renderView("ServeurController/addServer.html");
    }

    #[Post(path: "server/add", name: "serveur.saveServer")]
    public function saveServer()
    {
        $serveur = new Serveur();
        URequest::setValuesToObject($serveur);
        try {
            DAO::insert($serveur);
            $this->makeInitialTemplate();
            $this->index();
            //header("Location: ../../" . Router::path("index"));
        } catch (\Exception $e) {

        }
    }

    #[Post(path: "/server/ip/{name}", name: "serveur.getServerIpByName")]
    public function getServerIpByNamee($name)
    {
        UResponse::asJSON();
        $name = trim($name);
        echo json_encode(["ip" => empty($name) ? "" : gethostbyname($name)]);
    }

    #[Get(path: "server/{id}", name: "serveur.getResumeById")]
    public function getResumeById($id)
    {
        $serveur = DAO::getById(Serveur::class, $id);
        $form = $this->semantic->dataForm("updateServerForm", $serveur);
        $form->setFields(["description\n", "ipAddress", "port\n", "submit"]);
        $form->setCaptions(["Description du serveur", "Adresse IP", "Port", "Valider"]);
        $form->fieldAsSubmit(3, "violet");

        $vms = $this->semantic->dataTable("vmsOfServer", \StdClass::class, $serveur->getVms());
        $vms->setFields(["name", "ip", "sshPort", "os", "running"]);
        $vms->setCaptions(["Nom", "Adresse IP", "Port SSH", "OS", "Statut"]);


        $this->jquery->renderView("ServeurController/getResumeById.html", [
            "serveur" => $serveur
        ]);
    }
}
