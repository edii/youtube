<?php
/**
 * Подзагрузка класов
*/

return [
    // core
    'Request'  => '/core/Request.php',
    'Module'  => '/core/Module.php',
    'Component'  => '/core/Component.php',
    
    'Model'  => '/core/Model.php',
    'Controller'  => '/core/Controller.php',
    'View'  => '/core/View.php',
    
    // action
    'Action'    => '/core/actions/Action.php',
    'InlineAction'    => '/core/actions/InlineAction.php',
    'ViewAction'    => '/core/actions/ViewAction.php',
    
    'DatabaseConnection'        => '/core/database/database.php',
    'Database'                  => '/core/database/database.php',
    'DatabaseTransaction'       => '/core/database/database.php',
    'DatabaseStatementBase'     => '/core/database/database.php',
    'DatabaseStatementEmpty'    => '/core/database/database.php',
    'DatabaseLog'               => '/core/database/log.php',
    'DatabaseStatementPrefetch' => '/core/database/prefetch.php',
    'QueryConditionInterface'   => '/core/database/query.php',
    'DatabaseSchema'            => '/core/database/schema.php',


    'CDbCommandBuilder'     => '/core/database/CDbCommandBuilder.php',
    'CDatabase'             => '/core/database/CDatabase.php',
    'CDbException'          => '/core/database/CDbException.php',
    
    // youtube libs
    // 'Youtube' => '/libs/api/Youtube.php',   
];
