<?php
namespace dl\models\blogic;

use usr\models\entities\Role;

/**
 * @codeCoverageIgnore
 */
class ModuleInstallerPatcher extends ModuleInstaller
{
    public $patches = [
        '0.3.2.1' => [
            'features' => [
                [
                    'code' => '040.1.0.1',
                    'module' => 'dl',
                    'name' => '{75077D9E-3EBB-4245-8AAA-A99D6B7B01EA}',                 // [feature]
                    'description' => '{50CD88EE-28C0-47E8-A2C9-F6B06CD0C72B}',          // [feature]
                    'group' => '{64FFD68E-64F2-42E2-A0EE-8FA7801A8B20}',                // [feature] Отчеты
                    'roles' => [Role::ADMINISTRATOR, 'Training Manager', 'Training Manager Assistant', 'Manager'],
                    'navigation' => [
                        [
                            'section' => '{703A7D5E-D012-4264-893F-E810D9136323}',      // [navigation] Отчеты
                            'name' => '{25B57537-7373-4337-BBFC-47417806ADB5}',
                            'description' => '{E21A178E-D2B6-458A-9231-673C2F2FD071}',
                            'order' => '14',
                            'url' => '{moodle}/report/mocoquizaptitude/index.php',
                            'icon' => '',
                        ],
                    ],
                ],
            ],
        ],
    ];
}
