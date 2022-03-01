<?php

namespace controllers;

use Ajax\php\ubiquity\JsUtils;
use PHPMV\ProxmoxMaster;
use Ubiquity\core\postinstall\Display;
use Ubiquity\log\Logger;
use Ubiquity\themes\ThemesManager;

/**
 * Controller
 * @property JsUtils $jquery
 */
class IndexController extends ControllerBase
{

    public function index()
    {
        $vms = $this->api->getVMs();
        $dt = $this->semantic->dataTable('vms', \StdClass::class, $vms);
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
        /*		$defaultPage = Display::getDefaultPage();
                $links = Display::getLinks();
                $infos = Display::getPageInfos();

                $activeTheme = ThemesManager::getActiveTheme();
                $themes = Display::getThemes();
                if (\count($themes) > 0) {
                    $this->loadView('@activeTheme/main/vMenu.html', \compact('themes', 'activeTheme'));
                }
                $this->loadView($defaultPage, \compact('defaultPage', 'links', 'infos', 'activeTheme'));*/
    }

    public function ct($theme)
    {
        $themes = Display::getThemes();
        if ($theme != null && \array_search($theme, $themes) !== false) {
            $config = ThemesManager::saveActiveTheme($theme);
            \header('Location: ' . $config['siteUrl']);
        } else {
            Logger::warn('Themes', \sprintf('The theme %s does not exists!', $theme), 'changeTheme(ct)');
            $this->forward(IndexController::class);
        }
    }
}
