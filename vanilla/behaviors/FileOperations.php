<?php
/* Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 * 
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS  
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.                
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */
namespace Delphinium\Vanilla\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Classes\MigrationModel;
use RainLab\Builder\Classes\PluginCode;
use ApplicationException;
use Exception;
use Request;
use Flash;
use Input;
use Lang;

/**
 * Plugin version management functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexVersionsOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/classes/migrationmodel/management-fields.yaml';

    public function onVersionCreateOrOpen()
    {
        $versionNumber = Input::get('original_version');
        $pluginCodeObj = $this->getPluginCode();

        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $widget = $this->makeBaseFormWidget($versionNumber, $options);
        $this->vars['originalVersion'] = $versionNumber;

        if ($widget->model->isNewModel()) {
            $versionType = Input::get('version_type');
            $widget->model->initVersion($versionType);
        }

        $result = [
            'tabTitle' => $this->getTabName($versionNumber, $widget->model),
            'tabIcon' => 'icon-code-fork',
            'tabId' => $this->getTabId($pluginCodeObj->toCode(), $versionNumber),
            'isNewRecord' => $widget->model->isNewModel(),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode(),
                'originalVersion' => $versionNumber
            ])
        ];

        return $result;
    }

    public function onVersionSave()
    {
        $model = $this->loadOrCreateListFromPost();
        $model->fill($_POST);
        $model->save(false);

        Flash::success(Lang::get('rainlab.builder::lang.version.saved'));
        $result = $this->controller->widget->versionList->updateList();

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($model->getPluginCodeObj()->toCode(), $model->version),
            'tabTitle' => $this->getTabName($model->version, $model),
            'savedVersion' => $model->version,
            'isApplied' => $model->isApplied()
        ];

        return $result;
    }

    public function onVersionDelete()
    {
        $model = $this->loadOrCreateListFromPost();

        $model->deleteModel();

        return $this->controller->widget->versionList->updateList();
    }

    public function onVersionApply()
    {
        // Save the version before applying it
        //
        $model = $this->loadOrCreateListFromPost();
        $model->fill($_POST);
        $model->save(false);

        // Apply the version
        //
        $model->apply();

        Flash::success(Lang::get('rainlab.builder::lang.version.applied'));
        $result = $this->controller->widget->versionList->updateList();

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($model->getPluginCodeObj()->toCode(), $model->version),
            'tabTitle' => $this->getTabName($model->version, $model),
            'savedVersion' => $model->version
        ];

        return $result;
    }

    public function onVersionRollback()
    {
        // Save the version before rolling it back
        //
        $model = $this->loadOrCreateListFromPost();
        $model->fill($_POST);
        $model->save(false);

        // Rollback the version
        //
        $model->rollback();

        Flash::success(Lang::get('rainlab.builder::lang.version.rolled_back'));
        $result = $this->controller->widget->versionList->updateList();

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($model->getPluginCodeObj()->toCode(), $model->version),
            'tabTitle' => $this->getTabName($model->version, $model),
            'savedVersion' => $model->version
        ];

        return $result;
    }

    protected function loadOrCreateListFromPost()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));
        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $versionNumber = Input::get('original_version');

        return $this->loadOrCreateBaseModel($versionNumber, $options);
    }

    protected function getTabName($version, $model)
    {
        $pluginName = Lang::get($model->getModelPluginName());

        if (!strlen($version)) {
            return $pluginName.'/'.Lang::get('rainlab.builder::lang.version.tab_new_version');
        }

        return $pluginName.'/v'.$version;
    }

    protected function getTabId($pluginCode, $version)
    {
        if (!strlen($version)) {
            return 'version-'.$pluginCode.'-'.uniqid(time());
        }

        return 'version-'.$pluginCode.'-'.$version;
    }

    protected function loadOrCreateBaseModel($versionNumber, $options = [])
    {
        $model = new MigrationModel();

        if (isset($options['pluginCode'])) {
            $model->setPluginCode($options['pluginCode']);
        }

        if (!$versionNumber) {
            return $model;
        }

        $model->load($versionNumber);
        return $model;
    }
}