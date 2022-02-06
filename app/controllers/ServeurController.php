<?php

namespace controllers;

use Ajax\semantic\components\validation\Rule;
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
        $dt = $this->jquery->semantic()->dataTable("servers", \StdClass::class, DAO::getAll(Serveur::class));
        $dt->setFields(["dnsName", "ipAddress", "login", "VMs"]);
        $dt->setCaptions(["Nom du serveur", "Adresse IP", "Identifiant", "Nombre de VMs"]);
        $dt->setStriped();
        $dt->setCelled();
        $this->jquery->renderView("ServeurController/index.html");
        /*$this->loadView("ServeurController/index.html");*/
    }

    #[Get(path: "server/add", name: "serveur.addServer")]
    public function addServer()
    {
        $div = $this->jquery->semantic()->htmlDivider("form-container");
        $form = $this->jquery->semantic()->dataForm('addServerForm', new Serveur());
        $form->setFields(["dnsName\n", "ipAddress\n", "login", "password\n", "btSubmit"]); //Select fields to update
        $form->fieldAsInput(0, ["rules" => [Rule::not("", "Veuillez saisir le nom du serveur")]]);
        $form->fieldAsInput(1, ["rules" => [Rule::not("", "Veuillez saisir l'adresse IP du serveur")]]);
        $form->fieldAsInput(2, ["rules" => [Rule::not("", "Veuillez saisir un identifiant de connexion")]]);
        $form->fieldAsInput(3, ["inputType" => "password", "rules" => [["empty", "Veuillez saisir un mot de passe de connexion"]]]);
        $form->setCaptions(["Nom du serveur", "IP du serveur", "Identifiant", "Mot de passe"]);
        $form->setValidationParams(["on" => "blur", "inline" => true]);
        $form->setProperty("method", "POST");
        $form->setProperty("action", Router::path("serveur.saveServer"));
        $form->fieldAsSubmit("btSubmit", "blue", Router::path("serveur.saveServer"),
            'body', ["value" => "Valider", 'hasLoader'=>'internal']);
        /*$this->jquery->click("#form-submit", '$("#addServerForm").submit()');*/
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
}
