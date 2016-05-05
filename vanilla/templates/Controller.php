<?php
/**
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
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

namespace Delphinium\Vanilla\Templates;

use Delphinium\Vanilla\TemplateBase;

class Controller extends TemplateBase
{
    /**
     * @var array A mapping of stub to generated file.
     */
    protected $fileMap = [
        'controller/_instructions.stub' => 'controllers/{{lower_name}}/_{{lower_model}}instructions.htm',
        'controller/_list_toolbar.stub' => 'controllers/{{lower_name}}/_list_toolbar.htm',
        'controller/config_form.stub'   => 'controllers/{{lower_name}}/config_form.yaml',
        'controller/config_list.stub'   => 'controllers/{{lower_name}}/config_list.yaml',
        'controller/create.stub'        => 'controllers/{{lower_name}}/create.htm',
        'controller/index.stub'         => 'controllers/{{lower_name}}/index.htm',
        'controller/preview.stub'       => 'controllers/{{lower_name}}/preview.htm',
        'controller/update.stub'        => 'controllers/{{lower_name}}/update.htm',
        'controller/controller.stub'    => 'controllers/{{studly_name}}.php',
    ];
}