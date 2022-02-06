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
        $this->loadView("ServeurController/index.html");
    }

    #[Get(path: "server/add", name: "serveur.addServer")]
    public function addServer()
    {
        $div = $this->jquery->semantic()->htmlDivider("form-container");
        $form = $this->jquery->semantic()->dataForm('addServerForm', new Serveur());
        $form->setFields(["dnsName\n", "ipAddress\n", "login", "password\n", "btSubmit"]); //Select fields to update
        $form->fieldAsInput('dnsName', ["rules" => [Rule::not("", "Veuillez saisir le nom du serveur")]]);
        $form->fieldAsInput('ipAddress', ["rules" => [Rule::not("", "Veuillez saisir l'adresse IP du serveur")]]);
        $form->fieldAsInput('login', ["rules" => [Rule::not("", "Veuillez saisir un identifiant de connexion")]]);
        $form->fieldAsInput('password', ["inputType" => "password", "rules" => ["empty"]]);
        $form->setCaptions(["Nom du serveur", "IP du serveur", "Identifiant", "Mot de passe"]);
        $form->setValidationParams(["on" => "blur", "inline" => true]);
        $form->setProperty("method", "POST");
        $form->setProperty("action", Router::path("serveur.saveServer"));
        $form->fieldAsSubmit("btSubmit", "blue", Router::path("serveur.saveServer"),
            '#content', ["value" => "Valider", 'hasLoader'=>'internal']);
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
            // Le contexte POST reste après la redirection
            //$this->forward("controllers\AdminController", "index", null, true, true);
            //$this->redirectToRoute("admin.getVms", null, true, true);

            // Le contexte POST reste après la redirection, mais en plus le nombre de serveurs ne bouge pas sur la vue
            // si initialize n'est pas appelé
            $this->initialize();
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
