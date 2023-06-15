<?php
/**
 * Core file  
 * 
 * PHP version 5
 * 
 * @category Core
 * 
 * @package Backend
 * 
 * @author EISGE <kalifast@eisge.com>
 * 
 * @license kalifast.com Kalifast
 * 
 * @version SVN: $Id$
 * 
 * @link kalifast.com
 */ 

/**
 * Core class 
 * 
 * @category Core
 * 
 * @package Backend
 * 
 * @author EISGE <kalifast@eisge.com>
 * 
 * @license kalifast.com Kalifast
 * 
 * @link kalifast.com
 */
class Core extends BaseApi
{

    /**
     * Ajout d'une application module mode
     * 
     * @return true
     */
    function addApplicationModuleMode()
    {
        $d = $this->checkParams(
            [
                'ei_api_application_id' => 'string',
                'ei_api_application_module_id' => 'string',
                'ei_api_application_module_mode_id' => 'string'
            ]
        );

        // On vérifie d'abord si l'association existe
        $s = $this->PDO->prepare(
            'SELECT * from ei_api_application_module_mode where 
            ei_api_application_id=:ei_api_application_id and 
            ei_api_application_module_id=:ei_api_application_module_id and 
            ei_api_application_module_mode_id=:ei_api_application_module_mode_id'
        );
        $s->execute(
            [
                'ei_api_application_id' => $d->ei_api_application_id,
                'ei_api_application_module_id' => $d->ei_api_application_module_id,
                'ei_api_application_module_mode_id' =>
                $d->ei_api_application_module_mode_id
            ]
        );
        $application_module_mode_exists = $s->fetch();

        if ($application_module_mode_exists != false) {
            // La ligne existe déjà, on renvoie une erreur
            $this->logError('Application module mode already exists', 0);
        } else {
            // On peut partir sur les insertions

            // On regarde d'abord si l'application existe
            $s = $this->PDO->prepare(
                'SELECT * from ei_api_application where ei_api_application_id=
                :ei_api_application_id'
            );
            $s->execute(
                [
                    'ei_api_application_id' => $d->ei_api_application_id
                ]
            );
            $application_exists = $s->fetch();

            if ($application_exists == false) {
                // L'application n'existe pas, on la créé donc
                $s = $this->PDO->prepare(
                    'INSERT into ei_api_application(ei_api_application_id, 
                    description, comment, created_at, updated_at, created_by,
                    updated_by) values(:ei_api_application_id, "", "", now(), now(), 
                    :user_id, :user_id)'
                );
                $s->execute(
                    [
                        'ei_api_application_id' => $d->ei_api_application_id,
                        'user_id' => $this->user['ei_user_id']
                    ]
                );
            }

            // On regarde ensuite si le module existe pour l'application
            $s = $this->PDO->prepare(
                'SELECT * from ei_api_application_module where 
                ei_api_application_id=:ei_api_application_id and 
                ei_api_application_module_id=:ei_api_application_module_id'
            );
            $s->execute(
                [
                    'ei_api_application_id' => $d->ei_api_application_id,
                    'ei_api_application_module_id' => 
                    $d->ei_api_application_module_id
                ]
            );
            $application_module_exists = $s->fetch();

            if ($application_module_exists == false) {
                // On ajoute donc le module pour l'application
                $s = $this->PDO->prepare(
                    'INSERT into ei_api_application_module(ei_api_application_id, 
                    ei_api_application_module_id, description, comment, created_at,
                    updated_at, created_by, updated_by) values(
                    :ei_api_application_id, :ei_api_application_module_id, "", 
                    "", now(), now(), :user_id, :user_id)'
                );
                $s->execute(
                    [
                        'ei_api_application_id' => $d->ei_api_application_id,
                        'ei_api_application_module_id' => 
                        $d->ei_api_application_module_id,
                        'user_id' => $this->user['ei_user_id']
                    ]
                );
            }

            /* On ajoute ensuite l'application module mode (on sait qu'elle n'existe 
            pas sinon on ne passe pas dans ce if ;) )*/
            $s = $this->PDO->prepare(
                'INSERT into ei_api_application_module_mode(ei_api_application_id,
                ei_api_application_module_id, ei_api_application_module_mode_id, 
                icon_class, description, comment, created_at, updated_at, 
                created_by, updated_by) values(:ei_api_application_id, 
                :ei_api_application_module_id, :ei_api_application_module_mode_id, 
                null, "", "", now(), now(), :user_id, :user_id)'
            );
            $s->execute(
                [
                    'ei_api_application_id' => $d->ei_api_application_id,
                    'ei_api_application_module_id' => 
                    $d->ei_api_application_module_id,
                    'ei_api_application_module_mode_id' => 
                    $d->ei_api_application_module_mode_id,
                    'user_id' => $this->user['ei_user_id']
                ]
            );
        }

        return true;
    }

    /**
     * Ajout d'une nouvelle livraison
     * 
     * @return true
     */
    function addDelivery()
    {
        $d = $this->checkParams(
            [
                'delivery_name' => 'string',
                'delivery_date' => 'string'
            ]
        );

        // On récupère l'id max de la livraison
        $s = $this->PDO->prepare(
            'SELECT max(ei_delivery_id) from ei_delivery'
        );
        $s->execute();
        $max_delivery_id = (int)($s->fetch()?:[0])[0]+1;
        /*
        if ($max_delivery_id == 0) {
            $max_delivery_id = 1;
        }
        */

        // Insertion de la nouvelle livraison
        $s = $this->PDO->prepare(
            'INSERT into ei_delivery(ei_delivery_id, delivery_name, 
            delivery_date, ref_delivery_type_status_id) values(:ei_delivery_id, :delivery_name, 
            :delivery_date, 1)'
        );
        $s->execute(
            [
                'ei_delivery_id' => $max_delivery_id,
                'delivery_name' => $d->delivery_name,
                'delivery_date' => $d->delivery_date
            ]
        );

        return true;
    }

    /**
     * Ajout d'une nouvelle library
     * 
     * @return true
     */
    function addLibrary()
    {
        $d = $this->checkParams(
            [
                'ei_api_library_id' => 'string',
                'description' => 'string'
            ]
        );

        // On vérifie que la library n'existe pas
        $s = $this->PDO->prepare(
            'SELECT * from ei_api_library where ei_api_library_id=
            :ei_api_library_id'
        );
        $s->execute(
            [
                'ei_api_library_id' => $d->ei_api_library_id
            ]
        );
        $library_exists = $s->fetch();

        if ($library_exists != false) {
            // On retourne une erreur
            $this->logError('Library already exists', 0);
        } else {
            // On ajoute la nouvelle library
            $s = $this->PDO->prepare(
                'INSERT into ei_api_library(ei_api_library_id, description,
                created_at, updated_at, created_by, updated_by) values(
                :ei_api_library_id, :description, now(), now(), :user_id, :user_id)'
            );
            $s->execute(
                [
                    'ei_api_library_id' => $d->ei_api_library_id,
                    'description' => $d->description,
                    'user_id' => $this->user['ei_user_id']
                ]
            );
        }

        return true;
    }

    /**
     * On ajoute une action dans la library choisie
     * 
     * @return true
     */
    function addLibraryAction()
    {
        $d = $this->checkParams(
            [
                'ei_api_library_id' => 'string',
                'ei_api_library_action_id' => 'string',
                'description' => 'string',
                'type' => 'string',
                'root_record' => 'string',
                'code_file' => 'string',
                'code_function' => 'string',
                'comment' => 'string',
                'json_sample' => 'html'
            ]
        );

        // On vérifie que l'action n'existe pas déjà dans la library
        $s = $this->PDO->prepare(
            'SELECT * from ei_api_library_action where ei_api_library_id=
            :ei_api_library_id and ei_api_library_action_id=
            :ei_api_library_action_id'
        );
        $s->execute(
            [
                'ei_api_library_id' => $d->ei_api_library_id,
                'ei_api_library_action_id' => $d->ei_api_library_action_id
            ]
        );
        $library_action_exists = $s->fetch();

        if ($library_action_exists != false) {
            // On prévient que l'action existe déjà
            $this->logError(
                'Action already exists in library '.
                $d->ei_api_library_id, 0
            );
        } else {
            // L'action n'existe pas, on peut donc l'insérer
            // On vérifie ensuite l'existence du fichier et de la classe
            if (!$this->classExist($d->code_file, $d->code_function) ) {
                $this->logError(
                    $d->code_file.".php ". $d->code_file.".".$d->code_function.
                    "() doesn't exist"
                );
                return false;
            } else {
                // Le fichier et la classe existent, on peut donc faire l'insertion
                $s = $this->PDO->prepare(
                    'INSERT into ei_api_library_action(ei_api_library_id, 
                    ei_api_library_action_id, description, type, root_record,
                    code_file, code_function, comment, created_at, updated_at,
                    created_by, updated_by, json_sample) values(:ei_api_library_id,
                    :ei_api_library_action_id, :description, :type, :root_record,
                    :code_file, :code_function, :comment, now(), now(), :user_id, 
                    :user_id, :json_sample)'
                );
                $s->execute(
                    [
                        'ei_api_library_id' => $d->ei_api_library_id,
                        'ei_api_library_action_id' => $d->ei_api_library_action_id,
                        'description' => $d->description,
                        'type' => $d->type,
                        'root_record' => $d->root_record,
                        'code_file' => $d->code_file,
                        'code_function' => $d->code_function,
                        'comment' => $d->comment,
                        'user_id' => $this->user['ei_user_id'],
                        'json_sample' => $d->json_sample
                    ]
                );
            }
        }

        return true;
    }

    /**
     * Ajout de la library et de l'action dans l'application module mode (si elles 
     * n'y sont pas déjà)
     * 
     * @return true
     */
    function addLibraryActionForApplicationModuleMode()
    {
        $d = $this->checkParams(
            [
                'ei_api_application_id' => 'string',
                'ei_api_application_module_id' => 'string',
                'ei_api_application_module_mode_id' => 'string',
                'ei_api_library_id' => 'string',
                'ei_api_library_action_id' => 'string'
            ]
        );

        // On vérifie que l'association n'existe pas déjà
        $s = $this->PDO->prepare(
            'SELECT * from ei_api_application_module_mode_library_action where 
            ei_api_application_id=:ei_api_application_id and 
            ei_api_application_module_id=:ei_api_application_module_id and 
            ei_api_application_module_mode_id=:ei_api_application_module_mode_id and 
            ei_api_library_id=:ei_api_library_id and ei_api_library_action_id=
            :ei_api_library_action_id'
        );
        $s->execute(
            [
                'ei_api_application_id' => $d->ei_api_application_id,
                'ei_api_application_module_id' => $d->ei_api_application_module_id,
                'ei_api_application_module_mode_id' => 
                $d->ei_api_application_module_mode_id,
                'ei_api_library_id' => $d->ei_api_library_id,
                'ei_api_library_action_id' => $d->ei_api_library_action_id
            ]
        );
        $library_action_exists = $s->fetch();

        if ($library_action_exists != false) {
            // La ligne existe déjà
            $this->logError(
                'Library action already exists in application module mode ', 0
            );
        } else {
            // On ajoute la ligne
            $s = $this->PDO->prepare(
                'INSERT into ei_api_application_module_mode_library_action(
                ei_api_application_id, ei_api_application_module_id, 
                ei_api_application_module_mode_id, ei_api_library_id,
                ei_api_library_action_id, created_at, updated_at, 
                created_by, updated_by) values(:ei_api_application_id,
                :ei_api_application_module_id, :ei_api_application_module_mode_id,
                :ei_api_library_id, :ei_api_library_action_id, now(), now(), 
                :user_id, :user_id)'
            );
            $s->execute(
                [
                    'ei_api_application_id' => $d->ei_api_application_id,
                    'ei_api_application_module_id' => 
                    $d->ei_api_application_module_id,
                    'ei_api_application_module_mode_id' => 
                    $d->ei_api_application_module_mode_id,
                    'ei_api_library_id' => $d->ei_api_library_id,
                    'ei_api_library_action_id' => $d->ei_api_library_action_id,
                    'user_id' => $this->user['ei_user_id']
                ]
            );
        }

        return true;
    }

    /**
     * Ajout d'une nouvelle tache dans l'intervention
     * 
     * @return true
     */
    function addNewTaskToSubject()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ref_task_type_id' => 'int',
                'ref_task_type_status_id' => 'int',
                'title' => 'html',
                'description' => 'html',
                'ei_user_id' => 'int',
                'estimation' => 'int',
                'final_cost' => 'int',
                'expected_end' => 'html',
                'expected_start' => 'html'
            ]
        );

        // error_log($d->ei_user_id);
        $title = urldecode($d->title);
        $description = urldecode($d->description);

        // Récupération de l'id max de la nouvelle tache
        $s = $this->PDO->prepare(
            'SELECT max(ei_task_id) from ei_task'
        );
        $s->execute();
        $max_task_id = (int)($s->fetch()?:[0])[0]+1;
        /*
        if ($max_task_id == 0) {
            $max_task_id = 1;
        }
        */
        if ($d->expected_end == '') {
            $d->expected_end = '0000-00-00';
        }

        if ($d->expected_start == '') {
            $d->expected_start = '0000-00-00';
        }

        if ($d->ei_user_id === 1) {
            $user = $this->user['ei_user_id'];
        } else {
            $user = $d->ei_user_id;
        }

        $s = $this->PDO->prepare(
            'INSERT into ei_task(ei_task_id, ref_task_type_id, ref_task_status_id,
            title, description, ei_user_id, estimation, final_cost, creator_id, created_at, expected_end, expected_start) values(:ei_task_id,
            :ref_task_type_id, :ref_task_type_status_id, :title, :description, 
            :ei_user_id, :estimation, :final_cost, :creator_id, NOW(), :expected_end, :expected_start)'
        );
        $s->execute(
            [
                'ei_task_id' => $max_task_id,
                'ref_task_type_id' => $d->ref_task_type_id,
                'ref_task_type_status_id' => $d->ref_task_type_status_id,
                'title' => $title,
                'description' => $description,
                'ei_user_id' => $user,
                'estimation' => $d->estimation,
                'final_cost' => $d->final_cost,
                'creator_id' => $this->user['ei_user_id'],
                'expected_end' => $d->expected_end,
                'expected_start' => $d->expected_start
            ]
        );

        // Récupération de l'id max du lien de la tache
        $s = $this->PDO->prepare(
            'SELECT max(ei_task_link_id) from ei_task_link'
        );
        $s->execute();
        $max_task_link_id = (int)($s->fetch()?:[0])[0]+1;
        /*
        if ($max_task_link_id == 0) {
            $max_task_link_id = 1;
        }
        */

        // error_log($max_task_id);
        // error_log($max_task_link_id);
        // error_log($d->ei_subject_id);
        // Insertion du lien entre la tache et l'intervention
        $s = $this->PDO->prepare(
            'INSERT into ei_task_link(ei_task_id, ei_task_link_id, 
            ref_object_type_id, object_id) values(:ei_task_id, :ei_task_link_id,
            "SUBJECT", :ei_subject_id)'
        );
        $s->execute(
            [
                'ei_task_id' => $max_task_id,
                'ei_task_link_id' => $max_task_link_id,
                'ei_subject_id' => $d->ei_subject_id
            ]
        );

        $this->callClass(
            "Subject", 
            "addSubjectAudit", 
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_description' => "created task T-".$max_task_id,
                'element_type' => "TASK",
                'element_id' => $max_task_id,
                'label' => $title,
                'action' => "ADD"

            ]
        );

        $this->callClass(
            "Task", 
            "addFlagToTask", 
            [
                'ei_task_id' => $max_task_id,
                'ref_flag_id' => 1,

            ]
        );


        $this->setData($max_task_id);
        return true;
    }

    /**
     * Ajout d'une nouvelle permission (si elle n'existe pas encore)
     * 
     * @return true
     */
    function addPermission()
    {
        $d = $this->checkParams(
            [
                'ei_api_permission_id' => 'string'
            ]
        );

        // On vérifie que la permission n'existe pas déjà
        $s = $this->PDO->prepare(
            'SELECT * from ei_api_permission where ei_api_permission_id=
            :ei_api_permission_id'
        );
        $s->execute(
            [
                'ei_api_permission_id' => $d->ei_api_permission_id
            ]
        );
        $permission_exists = $s->fetch();

        if ($permission_exists != false) {
            // La permission existe déjà, on renvoie une erreur
            $this->logError(
                'Permission already exists', 0
            );
        } else {
            // On ajoute la nouvelle permission
            $s = $this->PDO->prepare(
                'INSERT into ei_api_permission(ei_api_permission_id,
                description, comment, created_by, updated_by, 
                created_at, updated_at) values(:ei_api_permission_id,
                "", "", :user_id, :user_id, now(), now())'
            );
            $s->execute(
                [
                    'ei_api_permission_id' => $d->ei_api_permission_id,
                    'user_id' => $this->user['ei_user_id']
                ]
            );
        }

        return true;
    }

    /**
     * Ajout d'une permission pour l'application module mode (si elle n'existe pas)
     * 
     * @return true
     */
    function addPermissionForApplicationModuleMode()
    {
        $d = $this->checkParams(
            [
                'ei_api_application_id' => 'string',
                'ei_api_application_module_id' => 'string',
                'ei_api_application_module_mode_id' => 'string',
                'ei_api_permission_id' => 'string'
            ]
        );

        // On vérifie que la permission n'est pas déjà associée
        $s = $this->PDO->prepare(
            'SELECT * from ei_api_application_module_mode_permission where 
            ei_api_application_id=:ei_api_application_id and 
            ei_api_application_module_id=:ei_api_application_module_id and 
            ei_api_application_module_mode_id=:ei_api_application_module_mode_id and 
            ei_api_permission_id=:ei_api_permission_id'
        );
        $s->execute(
            [
                'ei_api_application_id' => $d->ei_api_application_id,
                'ei_api_application_module_id' => $d->ei_api_application_module_id,
                'ei_api_application_module_mode_id' => 
                $d->ei_api_application_module_mode_id,
                'ei_api_permission_id' => $d->ei_api_permission_id
            ]
        );
        $permission_exists = $s->fetch();

        if ($permission_exists != false) {
            // La permission existe déjà, on renvoie l'erreur
            $this->logError(
                'Permission already exists in application module mode ', 0
            );
        } else {
            // On ajoute la permission
            $s = $this->PDO->prepare(
                'INSERT into ei_api_application_module_mode_permission(
                ei_api_application_id, ei_api_application_module_id,
                ei_api_application_module_mode_id, ei_api_permission_id,
                created_at, updated_at, created_by, updated_by) value(
                :ei_api_application_id, :ei_api_application_module_id,
                :ei_api_application_module_mode_id, :ei_api_permission_id,
                now(), now(), :user_id, :user_id)'
            );
            $s->execute(
                [
                    'ei_api_application_id' => $d->ei_api_application_id,
                    'ei_api_application_module_id' => 
                    $d->ei_api_application_module_id,
                    'ei_api_application_module_mode_id' => 
                    $d->ei_api_application_module_mode_id,
                    'ei_api_permission_id' => $d->ei_api_permission_id,
                    'user_id' => $this->user['ei_user_id']
                ]
            );
        }

        return true;
    }

    /** 
     * Ajout d'une permission au rôle choisi
     * 
     * @return true
     */
    function addPermissionToRole()
    {
        $d = $this->checkParams(
            [
                'ei_api_permisskljklkjkion_id' => 'string',
                'ei_api_rfdsdsdsole_id' => 'string'
            ]
        );

        // On vérifie que le rôle n'a pas déjà cette permission
        $s = $this->PDO->prepare(
            'SELECT * from ei_api_role_permission where ei_api_permission_id=
            :ei_api_permission_id and ei_api_role_id=:ei_api_role_id'
        );
        $s->execute(
            [
                'ei_api_permission_id' => $d->ei_api_permission_id,
                'ei_api_role_id' => $d->ei_api_role_id
            ]
        );
        $role_permission_exists = $s->fetch();

        if ($role_permission_exists != false) {
            // La permission est déjà associée au rôle, on renvoie une erreur
            $this->logError(
                'Permission already exists for role', 0
            );
        } else {
            // On insère la nouvelle permission pour le rôle
            $s = $this->PDO->prepare(
                'INSERT into ei_api_role_permission(ei_api_role_id, 
                ei_api_permission_id, created_by, updated_by, 
                created_at, updated_at) values(:ei_api_role_id, 
                :ei_api_permission_id, :user_id, :user_id, now(), 
                now())'
            );
            $s->execute(
                [
                    'ei_api_role_id' => $d->ei_api_role_id,
                    'ei_api_permission_id' => $d->ei_api_permission_id,
                    'user_id' => $this->user['ei_user_id']
                ]
            );
        }

        return true;
    }

    /**
     * Ajout d'une nouvelle pool
     * 
     * @return true
     */
    function addPool()
    {
        $d = $this->checkParams(
            [
                'name' => 'string',
                'color' => 'string',
                'order' => 'int'
            ]
        );

        // On vérfie que le nom de la pool n'existe pas
        $s = $this->PDO->prepare(
            'SELECT * from ei_pool where pool_name=:name'
        );
        $s->execute(
            [
                'name' => $d->name
            ]
        );
        $pool_exists = $s->fetch();

        if ($pool_exists != false) {
            $this->logError(
                'Pool already exists', 0
            );
        } else {
            // On récupère l'id max de pool
            $s = $this->PDO->prepare(
                'SELECT max(ei_pool_id) from ei_pool'
            );
            $s->execute();
            $max_pool_id = (int)($s->fetch()?:[0])[0]+1;
            /*
            if ($max_pool_id == 0) {
                $max_pool_id = 1;
            }
            */
            
            if ($d->order == 0) {
                // On récupère l'ordre max de pool
                $s = $this->PDO->prepare(
                    'SELECT max(`order`) from ei_pool'
                );
                $s->execute();
                $max_pool_order = (int)($s->fetch()?:[0])[0]+1;
                /*
                if ($max_pool_order == 0) {
                    $max_pool_order = 1;
                }
                */
            } else {
                $max_pool_order = $d->order;
            }

            $s = $this->PDO->prepare(
                'INSERT into ei_pool(ei_pool_id, pool_name, default_subject_type,
                default_subject_status, default_subject_priority, `order`, pool_color) 
                values(:ei_pool_id, :pool_name, null, null, null, :order, :color)'
            );
            $s->execute(
                [
                    'ei_pool_id' => $max_pool_id,
                    'pool_name' => $d->name,
                    'order' => $max_pool_order,
                    'color' => $d->color
                ]
            );
        }

        return true;
    }

    /**
     * Ajout d'une priorité d'intervention dans la pool
     * 
     * @return true
     */
    function addPoolSubjectPriority()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_priority_id' => 'int',
                'order' => 'int'
            ]
        );

        // On vérifie que la priorité d'intervention n'est pas déjà dans la pool
        $s = $this->PDO->prepare(
            'SELECT * from ei_pool_subject_priority where 
            ei_pool_id=:ei_pool_id and ref_subject_priority_id=:ref_subject_priority_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_priority_id' => $d->ref_subject_priority_id
            ]
        );
        $pool_subject_priority_exists = $s->fetch();

        if ($pool_subject_priority_exists != false) {
            $this->logError(
                'Subject priority already exists in pool', 0
            );
        } else {
            if ($d->order == 0) {
                // On récupère l'ordre max
                $s = $this->PDO->prepare(
                    'SELECT max(`order`) from ei_pool_subject_priority where 
                    ei_pool_id=:ei_pool_id'
                );
                $s->execute(
                    [
                        'ei_pool_id' => $d->ei_pool_id
                    ]
                );
                $max_pool_subject_priority_order = (int)($s->fetch()?:[0])[0]+1;
                /*
                if ($max_pool_subject_priority_order == 0) {
                    $max_pool_subject_priority_order = 1;
                }
                */
            } else {
                $max_pool_subject_priority_order = $d->order;
            }

            $s = $this->PDO->prepare(
                'INSERT into ei_pool_subject_priority(ei_pool_id, 
                ref_subject_priority_id, `order`) values(:ei_pool_id,
                :ref_subject_priority_id, :order)'
            );
            $s->execute(
                [
                    'ei_pool_id' => $d->ei_pool_id,
                    'ref_subject_priority_id' => $d->ref_subject_priority_id,
                    'order' => $max_pool_subject_priority_order
                ]
            );
        }

        return true;
    }

    /**
     * Ajout d'un status d'intervention pour le type d'intervention de la pool
     * 
     * @return true
     */
    function addPoolSubjectStatus()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int',
                'ref_subject_status_id' => 'int',
                'status_name' => 'string',
                'is_final' => 'string',
                'order' => 'int'
            ]
        );

        // On vérifie que le status d'intervention n'est pas déjà dans le type 
        // d'intervention de la pool
        $s = $this->PDO->prepare(
            'SELECT * from ei_pool_subject_status where ei_pool_id=:ei_pool_id 
            and ref_subject_type_id=:ref_subject_type_id and 
            ref_subject_status_id=:ref_subject_status_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ref_subject_status_id' => $d->ref_subject_status_id
            ]
        );
        $pool_subject_status_exists = $s->fetch();

        if ($pool_subject_status_exists != false) {
            $this->logError(
                'Subject status already exists in pool subject type', 0
            );
        } else {
            if ($d->order == 0) {
                // On récupère l'ordre max
                $s = $this->PDO->prepare(
                    'SELECT max(`order`) from ei_pool_subject_status where 
                    ei_pool_id=:ei_pool_id and ref_subject_type_id=:ref_subject_type_id'
                );
                $s->execute(
                    [
                        'ei_pool_id' => $d->ei_pool_id,
                        'ref_subject_type_id' => $d->ref_subject_type_id
                    ]
                );
                $pool_subject_status_max_order = (int)($s->fetch()?:[0])[0]+1;
                /*
                if ($pool_subject_status_max_order == 0) {
                    $pool_subject_status_max_order = 1;
                }
                */
            } else {
                $pool_subject_status_max_order = $d->order;   
            }

            $s = $this->PDO->prepare(
                'INSERT into ei_pool_subject_status(ei_pool_id, ref_subject_type_id,
                ref_subject_status_id, status_name, is_final, `order`) values(
                :ei_pool_id, :ref_subject_type_id, :ref_subject_status_id, 
                :status_name, :is_final, :order)'
            );
            $s->execute(
                [
                    'ei_pool_id' => $d->ei_pool_id,
                    'ref_subject_type_id' => $d->ref_subject_type_id,
                    'ref_subject_status_id' => $d->ref_subject_status_id,
                    'status_name' => $d->status_name,
                    'is_final' => $d->is_final,
                    'order' => $pool_subject_status_max_order
                ]
            );
        }

        return true;
    }

    /**
     * Ajout d'un type d'intervention dans la pool
     * 
     * @return true
     */
    function addPoolSubjectType()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int',
                'order' => 'int'
            ]
        );

        // On vérifie que le type d'intervention n'est pas déjà dans la pool 
        $s = $this->PDO->prepare(
            'SELECT * from ei_pool_subject_type where 
            ei_pool_id=:ei_pool_id and ref_subject_type_id=:ref_subject_type_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id
            ]
        );
        $pool_subject_type_exists = $s->fetch();

        if ($pool_subject_type_exists != false) {
            $this->logError(
                'Subject type already exists in pool', 0
            );
        } else {
            if ($d->order == 0) {
                // On récupère l'ordre max
                $s = $this->PDO->prepare(
                    'SELECT max(`order`) from ei_pool_subject_type where 
                    ei_pool_id=:ei_pool_id'
                );
                $s->execute(
                    [
                        'ei_pool_id' => $d->ei_pool_id
                    ]
                );
                $max_pool_subject_type_order = (int)($s->fetch()?:[0])[0]+1;
                /*
                if ($max_pool_subject_type_order == 0) {
                    $max_pool_subject_type_order = 1;
                }
                */
            } else {
                $max_pool_subject_type_order = $d->order;
            }

            $s = $this->PDO->prepare(
                'INSERT into ei_pool_subject_type(ei_pool_id, 
                ref_subject_type_id, `order`) values(:ei_pool_id,
                :ref_subject_type_id, :order)'
            );
            $s->execute(
                [
                    'ei_pool_id' => $d->ei_pool_id,
                    'ref_subject_type_id' => $d->ref_subject_type_id,
                    'order' => $max_pool_subject_type_order
                ]
            );
        }

        return true;
    }

    /**
     * Ajout d'un type de campagne d'intervention pour le type d'intervention de la pool
     * 
     * @return true
     */
    function addPoolSubjectTypeCampaignType()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int',
                'name' => 'string'
            ]
        );

        // Récupération de l'id max du nouveau type de campagne d'intervention
        $s = $this->PDO->prepare(
            'SELECT max(ei_subject_campaigntype_id) from ei_subject_campaigntype'
        );
        $s->execute();
        $max_subject_campaigntype_id = (int)($s->fetch()?:[0])[0]+1;
        /*
        if ($max_subject_campaigntype_id == 0) {
            $max_subject_campaigntype_id = 1;
        }
        */

        $s = $this->PDO->prepare(
            'INSERT into ei_subject_campaigntype(ei_pool_id, ref_subject_type_id, ei_subject_campaigntype_id, name)
            values(:ei_pool_id, :ref_subject_type_id, :ei_subject_campaigntype_id, :name)'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ei_subject_campaigntype_id' => $max_subject_campaigntype_id,
                'name' => $d->name
            ]
        );

        return true;
    }

    /**
     * Ajout d'un type de tache pour le type d'intervention de la pool
     * 
     * @return true
     */
    function addPoolSubjectTypeTaskType()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int',
                'ref_task_type_id' => 'int',
                'default_title' => 'string',
                'created_by_default' => 'string',
                'default_estimation' => 'int',
                'default_user_id' => 'int',
                'default_task_status_id' => 'int',
                'order' => 'int'
            ]
        );

        // On vérifie que le type de tache n'existe pas déjà pour le type d'intervention
        // de la pool
        $s = $this->PDO->prepare(
            'SELECT * from ei_pool_subject_type_task_type where ei_pool_id=:ei_pool_id 
            and ref_subject_type_id=:ref_subject_type_id and ref_task_type_id=
            :ref_task_type_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ref_task_type_id' => $d->ref_task_type_id
            ]
        );
        $task_exists = $s->fetch();

        if ($task_exists != false) {
            $this->logError(
                'Task type already exists in pool subject type', 0
            );
        } else {
            // On récupère l'ordre max
            if ($d->order == 0) {
                $s = $this->PDO->prepare(
                    'SELECT max(`order`) from ei_pool_subject_type_task_type'
                );
                $s->execute();
                $task_type_order_max = (int)($s->fetch()?:[0])[0]+1;
                /*
                if ($task_type_order_max == 0) {
                    $task_type_order_max = 1;
                }
                */
            } else {
                $task_type_order_max = $d->order;
            }

            $s = $this->PDO->prepare(
                'INSERT into ei_pool_subject_type_task_type(ei_pool_id, 
                ref_subject_type_id, ref_task_type_id, default_title, 
                created_by_default, default_estimation, default_user_id, 
                default_task_status_id, `order`) values(:ei_pool_id, 
                :ref_subject_type_id, :ref_task_type_id, :default_title, 
                :created_by_default, :default_estimation, :default_user_id, 
                :default_task_status_id, :order)'
            );
            $s->execute(
                [
                    'ei_pool_id' => $d->ei_pool_id,
                    'ref_subject_type_id' => $d->ref_subject_type_id,
                    'ref_task_type_id' => $d->ref_task_type_id,
                    'default_title' => $d->default_title,
                    'created_by_default' => $d->created_by_default,
                    'default_estimation' => $d->default_estimation,
                    'default_user_id' => $d->default_user_id,
                    'default_task_status_id' => $d->default_task_status_id,
                    'order' => $task_type_order_max
                ]
            );
        }

        return true;
    }

    /**
     * Ajout d'un statut de tache pour le type de tache du type d'intervention de la pool
     * 
     * @return true
     */
    function addPoolSubjectTypeTaskTypeTaskStatus()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int',
                'ref_task_type_id' => 'int',
                'ref_task_status_id' => 'int',
                'order' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT * from ei_pool_subject_type_task_type_status where ei_pool_id=:ei_pool_id and ref_subject_type_id=:ref_subject_type_id and 
            ref_task_type_id=:ref_task_type_id and ref_task_status_id=:ref_task_status_id'
        );

        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ref_task_type_id' => $d->ref_task_type_id,
                'ref_task_status_id' => $d->ref_task_status_id
            ]
        );

        $task_status_exists = $s->fetch();

        if ($task_status_exists != false) {
            $this->logError(
                'Task status already exists in pool subject type task type', 0
            );
        } else {
            $s = $this->PDO->prepare(
                'INSERT into ei_pool_subject_type_task_type_status(ei_pool_id, ref_subject_type_id, ref_task_type_id, ref_task_status_id, `order`) values(:ei_pool_id,
                :ref_subject_type_id, :ref_task_type_id, :ref_task_status_id, :order)'
            );
            $s->execute(
                [
                    'ei_pool_id' => $d->ei_pool_id,
                    'ref_subject_type_id' => $d->ref_subject_type_id,
                    'ref_task_type_id' => $d->ref_task_type_id,
                    'ref_task_status_id' => $d->ref_task_status_id,
                    'order' => $d->order
                ]
            );
        }
        
        return true;
    }



    /**
     * Ajout d'un nouveau rôle
     * 
     * @return true
     */
    function addRole()
    {
        $d = $this->checkParams(
            [
                'ei_api_role_id' => 'string'
            ]
        );

        if ($d->ei_api_role_id == 'SUPERMAN') {
            $this->logError('SUPERMAN is a reserved name for kalifast system', 0);
        } else {
            // On vérifie que le rôle n'existe pas déjà
            $s = $this->PDO->prepare(
                'SELECT * from ei_api_role where ei_api_role_id=:ei_api_role_id'
            );
            $s->execute(
                [
                    'ei_api_role_id' => $d->ei_api_role_id
                ]
            );
            $role_exists = $s->fetch();

            if ($role_exists != false) {
                // Le rôle existe, on renvoie une erreur
                $this->logError(
                    'Role already exists', 0
                );
            } else {
                // On insère le nouveau rôle
                $s = $this->PDO->prepare(
                    'INSERT into ei_api_role(ei_api_role_id, description, comment, 
                    created_at, updated_at, created_by, updated_by) values(
                    :ei_api_role_id, "", "", now(), now(), :user_id, :user_id)'
                );
                $s->execute(
                    [
                        'ei_api_role_id' => $d->ei_api_role_id,
                        'user_id' => $this->user['ei_user_id']
                    ]
                );
            } 
        }

        
        return true;
    }

    /**
     * Ajout d'un rôle à l'utilisateur choisi
     * 
     * @return true
     */
    function addRoleToUser() 
    {
        $d = $this->checkParams(
            [
                'ei_api_role_id' => 'string',
                'ei_user_id' => 'int'
            ]
        );
        if ($d->ei_api_role_id == 'SUPERMAN') {
            $this->logError(
                'SUPERMAN role is inherited from kalifast.com', 0
            );
        } else {
            // On vérifie que le rôle n'est pas déjà assigné à l'utilisateur
            $s = $this->PDO->prepare(
                'SELECT * from ei_api_role_user where ei_api_role_id=
                :ei_api_role_id and ei_user_id=:ei_user_id'
            );
            $s->execute(
                [
                    'ei_api_role_id' => $d->ei_api_role_id,
                    'ei_user_id' => $d->ei_user_id
                ]
            );
            $user_role_exists = $s->fetch();

            if ($user_role_exists != false) {
                // Le role existe, on renvoie une erreur
                $this->logError(
                    'Role already exists for chosen user', 0
                );
            } else {
                // On peut ajouter le rôle à l'utilisateur
                $s = $this->PDO->prepare(
                    'INSERT into ei_api_role_user(ei_api_role_id, ei_user_id, 
                    created_at, updated_at, created_by, updated_by) values(
                    :ei_api_role_id, :ei_user_id, now(), now(), :user_id,
                    :user_id)'
                );
                $s->execute(
                    [
                        'ei_api_role_id' => $d->ei_api_role_id,
                        'ei_user_id' => $d->ei_user_id,
                        'user_id' => $this->user['ei_user_id']
                    ]
                );
            }
        }

        

        return true;
    }

    /**
     * Ajout d'un scénario dans la campagne d'intervention
     * 
     * @return true
     */
    function addScenarioInSubjectCampaign()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_subject_campaign_id' => 'int',
                'ei_scenario_id' => 'int',
                'max_campaign_step_order'=> 'int'
            ]
        );

        $s = $this->PDO->prepare(
            "UPDATE `ei_campaign_step` 
            SET 
                `ei_campaign_step_order` = ei_campaign_step_order+1
            WHERE
                `ei_campaign_id` = :ei_subject_campaign_id
                    AND `ei_campaign_version_id` = '0' and ei_campaign_step_order >=:max_campaign_step_order"
        );
        $s->execute(
            [
                'ei_subject_campaign_id' => $d->ei_subject_campaign_id,
                'max_campaign_step_order' => $d->max_campaign_step_order,
            ]
        );
        // $max_campaign_version_id= (int)($s->fetch()?:[0])[0];
        $max_campaign_version_id= 0;


        $s = $this->PDO->prepare(
            'SELECT max(ei_campaign_step_id) from ei_campaign_step where ei_campaign_id=:ei_subject_campaign_id;'
        );
        $s->execute(
            [
                'ei_subject_campaign_id' => $d->ei_subject_campaign_id,
            ]
        );
        $max_campaign_step_id= (int)($s->fetch()?:[0])[0]+1;
        if ($max_campaign_step_id === 0) {
            $max_campaign_step_id = 1;
        }

        $s = $this->PDO->prepare(
            'SELECT scenario_name FROM ei_scenario where ei_scenario_id=:ei_scenario_id'
        );
        $s->execute(
            [
                'ei_scenario_id' => $d->ei_scenario_id,
            ]
        );
        $scenario_name= $s->fetch(PDO::FETCH_ASSOC);

        $s = $this->PDO->prepare(
            "INSERT INTO `ei_campaign_step` (`ei_campaign_id`, `ei_campaign_version_id`, `ei_campaign_step_id`, `ei_campaign_step_order`, `ei_campaign_step_type`, `ei_campaign_step_scenario_id`, `ei_campaign_step_dataset_id`, `ei_campaign_step_text`) 
            VALUES (:ei_subject_campaign_id, :max_campaign_version_id, :max_campaign_step_id, :max_campaign_step_order, 'scenario', :ei_scenario_id, '1', :scenario_name)"
        );
        $s->execute(
            [
                'max_campaign_version_id' => $max_campaign_version_id,
                'ei_subject_campaign_id' => $d->ei_subject_campaign_id,
                'max_campaign_step_id' => $max_campaign_step_id,
                'max_campaign_step_order' => $d->max_campaign_step_order,
                'ei_scenario_id'=> $d->ei_scenario_id,
                'scenario_name'=>$scenario_name['scenario_name']
            ]
        );

        $this->callClass(
            "Subject", 
            "addSubjectAudit", 
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_description' => "added a scenario in subject campaign",
                'element_type' => "SCENARIO",
                'element_id' => $d->ei_scenario_id,
                'label' => '',
                'action' => "ADD"

            ]
        );


        return true;
    }

    /**
     * Ajout d'un step text dans la campagne d'intervention
     * 
     * @return true
     */
    function addTextStepCampaign()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_subject_campaign_id' => 'int',
                'textStep' => 'html',
                'max_campaign_step_order'=> 'int'
            ]
        );
        error_log($d->ei_subject_id);
        error_log($d->ei_subject_campaign_id);
        error_log($d->textStep);
        error_log($d->max_campaign_step_order);

        $s = $this->PDO->prepare(
            "UPDATE `ei_campaign_step` 
            SET 
                `ei_campaign_step_order` = ei_campaign_step_order+1
            WHERE
                `ei_campaign_id` = :ei_subject_campaign_id
                    AND `ei_campaign_version_id` = '0' and ei_campaign_step_order >=:max_campaign_step_order"
        );
        $s->execute(
            [
                'ei_subject_campaign_id' => $d->ei_subject_campaign_id,
                'max_campaign_step_order' => $d->max_campaign_step_order,
            ]
        );
        $max_campaign_version_id= 0;


        $s = $this->PDO->prepare(
            'SELECT max(ei_campaign_step_id) from ei_campaign_step where ei_campaign_id=:ei_subject_campaign_id;'
        );
        $s->execute(
            [
                'ei_subject_campaign_id' => $d->ei_subject_campaign_id,
            ]
        );
        $max_campaign_step_id= (int)($s->fetch()?:[0])[0]+1;
        if ($max_campaign_step_id === 0) {
            $max_campaign_step_id = 1;
        }

        $s = $this->PDO->prepare(
            "INSERT INTO `ei_campaign_step` (`ei_campaign_id`, `ei_campaign_version_id`, `ei_campaign_step_id`, `ei_campaign_step_order`, `ei_campaign_step_type`, `ei_campaign_step_scenario_id`, `ei_campaign_step_dataset_id`, `ei_campaign_step_text`) 
            VALUES (:ei_subject_campaign_id, :max_campaign_version_id, :max_campaign_step_id, :max_campaign_step_order, 'text','' ,'' , :textStep)"
        );
        $s->execute(
            [
                'max_campaign_version_id' => $max_campaign_version_id,
                'ei_subject_campaign_id' => $d->ei_subject_campaign_id,
                'max_campaign_step_id' => $max_campaign_step_id,
                'max_campaign_step_order' => $d->max_campaign_step_order,
                'textStep'=> $d->textStep,
            ]
        );

        return true;
    }


    /**
     * Ajout d'une nouvelle campagne d'intervention
     * 
     * @return true
     */
    function addSubjectCampaign()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_subject_campaign_type_id' => 'int',
                'ei_campaign_version_label' => 'string'
            ]
        );

        // Récupération de l'id max de la nouvelle campagne
        $s = $this->PDO->prepare(
            'SELECT max(ei_campaign_id) from ei_campaign'
        );
        $s->execute();
        $max_subject_campaign_id = (int)($s->fetch()?:[0])[0]+1;
        
        /*
        if ($max_subject_campaign_id == 0) {
            $max_subject_campaign_id = 1;
        }
        */
        $s = $this->PDO->prepare(
            "INSERT INTO `ei_campaign` (`ei_campaign_id`) VALUES (:ei_subject_campaign_id)"
        );
        $s->execute(
            [
                'ei_subject_campaign_id' => $max_subject_campaign_id,
            ]
        );

        $s = $this->PDO->prepare(
            "INSERT INTO `ei_campaign_version` (`ei_campaign_id`, `ei_campaign_version_id`, `ei_campaign_version_label`, `ei_campaign_version_created_by`) 
            VALUES (:ei_subject_campaign_id, '0', :ei_campaign_version_label, :user_id);"
        );
        $s->execute(
            [
                'ei_subject_campaign_id' => $max_subject_campaign_id,
                'ei_campaign_version_label' => $d->ei_campaign_version_label,
                'user_id' => $this->user['ei_user_id']
            ]
        );

        $s = $this->PDO->prepare(
            "INSERT INTO `ei_subject_campaign` (`ei_subject_id`, `ei_subject_campaign_type_id`, `ei_subject_campaign_id`) 
            VALUES (:ei_subject_id, :ei_subject_campaign_type_id, :ei_subject_campaign_id);"
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_subject_campaign_type_id' => $d->ei_subject_campaign_type_id,
                'ei_subject_campaign_id' => $max_subject_campaign_id,
            ]
        );

        $this->callClass(
            "Subject", 
            "addSubjectAudit", 
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_description' => "created a new subject campaign",
                'element_type' => "CAMPAIGN",
                'element_id' => $max_subject_campaign_id,
                'label' => '',
                'action' => "ADD"

            ]
        );


        return true;
    }

    /**
     * Ajout d'une nouvelle priorité d'intervention
     * 
     * @return true
     */
    function addSubjectPriority()
    {
        $d = $this->checkParams(
            [
                'priority_name' => 'string',
                'color' => 'string',
                'order' => 'int',
                'icon' => 'string'
            ]
        );

        // On vérifie que la priorité n'exista pas déjà
        $s = $this->PDO->prepare(
            'SELECT * from ref_subject_priority where priority_name=:priority_name'
        );
        $s->execute(
            [
                'priority_name' => $d->priority_name
            ]
        );
        $subject_priority_exists = $s->fetch();

        if ($subject_priority_exists != false) {
            // La priorité existe déjà
            $this->logError(
                'Subject priority already exists', 0
            );
        } else {
            // On récupère l'id max de la priorité
            $s = $this->PDO->prepare(
                'SELECT max(ref_subject_priority_id) from ref_subject_priority'
            );
            $s->execute();
            $max_subject_priority_id = (int)($s->fetch()?:[0])[0]+1;
            /*
            if ($max_subject_priority_id == 0) {
                $max_subject_priority_id = 1;
            }
            */

            if ($d->order == 0) {
                // On récupère l'ordre max de la priorité
                $s = $this->PDO->prepare(
                    'SELECT max(`order`) from ref_subject_priority'
                );
                $s->execute();
                $max_subject_priority_order = (int)($s->fetch()?:[0])[0]+1;
                /*
                if ($max_subject_priority_order == 0) {
                    $max_subject_priority_order = 1;
                }
                */
            } else {
                $max_subject_priority_order = $d->order;
            }

            // Insertion de la nouvelle priorité
            $s = $this->PDO->prepare(
                'INSERT into ref_subject_priority(ref_subject_priority_id,
                priority_name, color, `order`,priority_picto) values(:ref_subject_priority_id,
                :priority_name, :color, :order,:priority_picto)'
            );
            $s->execute(
                [
                    'ref_subject_priority_id' => $max_subject_priority_id,
                    'priority_name' => $d->priority_name,
                    'color' => $d->color,
                    'order' => $max_subject_priority_order,
                    'priority_picto' => $d->icon
                ]
            );
        }

        return true;
    }

    /**
     * Ajout d'un role d'intervention
     * 
     * @return true
     */
    function addSubjectRole()
    {
        $d = $this->checkParams(
            [
                'role_name' => 'string',
                'order' => 'int'
            ]
        );

        // On vérifie que le role n'existe pas déjà
        $s = $this->PDO->prepare(
            'SELECT * from ref_subject_role where role_name=:role_name'
        );
        $s->execute(
            [
                'role_name' => $d->role_name
            ]
        );
        $subject_role_exists = $s->fetch();

        if ($subject_role_exists != false) {
            // Le role existe déjà
            $this->logError(
                'Subject role already exists', 0
            );
        } else {
            // On récupère l'id max du nouveau role de l'intervention
            $s = $this->PDO->prepare(
                'SELECT max(ref_subject_role_id) from ref_subject_role'
            );
            $s->execute();
            $max_subject_role_id = (int)($s->fetch()?:[0])[0]+1;
            /*
            if ($max_subject_role_id == 0) {
                $max_subject_role_id = 1;
            }
            */

            if ($d->order == 0) {
                // On récupère l'order max du nouveau role de l'intervention
                $s = $this->PDO->prepare(
                    'SELECT max(`order`) from ref_subject_role'
                );
                $s->execute();
                $max_subject_role_order = (int)($s->fetch()?:[0])[0]+1;
                /*
                if ($max_subject_role_order == 0) {
                    $max_subject_role_order = 1;
                }
                */
            } else {
                $max_subject_role_order = $d->order;   
            }

            // Insertion du nouveau role de l'intervention
            $s = $this->PDO->prepare(
                'INSERT into ref_subject_role(ref_subject_role_id, role_name, `order`)
                values(:ref_subject_role_id, :role_name, :order)'
            );
            $s->execute(
                [
                    'ref_subject_role_id' => $max_subject_role_id,
                    'role_name' => $d->role_name,
                    'order' => $max_subject_role_order
                ]
            );
        }

        return true;
    }

    /**
     * Ajout d'un statut d'intervention
     * 
     * @return true
     */
    function addSubjectStatus()
    {
        $d = $this->checkParams(
            [
                'status_name' => 'string',
                'color' => 'string',
                'order' => 'int',
                'is_final' => 'string',
                'Icon' => 'string'
            ]
        );

        // On vérifie que le status n'existe pas déjà
        $s = $this->PDO->prepare(
            'SELECT * from ref_subject_status where status_name=:status_name'
        );
        $s->execute(
            [
                'status_name' => $d->status_name
            ]
        );
        $status_exists = $s->fetch();

        if ($status_exists != false) {
            // Le status existe déjà
            $this->logError(
                'Subject status already exists', 0
            );
        } else {
            // On récupère l'id max du nouveau status d'intervention
            $s = $this->PDO->prepare(
                'SELECT max(ref_subject_status_id) from ref_subject_status'
            );
            $s->execute();
            $max_subject_status_id = (int)($s->fetch()?:[0])[0]+1;
            /*
            if ($max_subject_status_id == 0) {
                $max_subject_status_id = 1;
            }
            */
            if ($d->order == 0) {
                // On récupère l'order du nouveau type d'intervention
                $s = $this->PDO->prepare(
                    'SELECT max(`order`) from ref_subject_status'
                );
                $s->execute();
                $max_subject_status_order = (int)($s->fetch()?:[0])[0]+1;
                /*
                if ($max_subject_status_order == 0) {
                    $max_subject_status_order = 1;
                }
                */
            } else {
                $max_subject_status_order = $d->order;
            }

            // Insertion du nouveau type d'intervention
            $s = $this->PDO->prepare(
                'INSERT into ref_subject_status(ref_subject_status_id, status_name,
                color, `order`, is_final, status_icon) values(:ref_subject_status_id, :status_name, :color, 
                :order, :is_final,:status_icon)'
            );
            $s->execute(
                [
                    'ref_subject_status_id' => $max_subject_status_id,
                    'status_name' => $d->status_name,
                    'color' => $d->color,
                    'order' => $max_subject_status_order,
                    'is_final' => $d->is_final,
                    'status_icon' => $d->Icon,
                ]
            );
        }

        return true;
    }

    /**
     * Ajout d'un type de tache d'intervention
     * 
     * @return true
     */
    function addSubjectTaskType()
    {
        $d = $this->checkParams(
            [
                'task_type_name' => 'string',
                'task_color' => 'string',
                'order' => 'int',
                'type_prefix' => 'string',
                'default_estimation' => 'float',
                'delay_finish_to_delivery' => 'int',
                'default_effort_by_day' => 'int'
            ]
        );

        // On vérifie que le type de tâche n'existe pas déjà
        $s = $this->PDO->prepare(
            'SELECT * from ref_task_type where task_type_name=:task_type_name'
        );
        $s->execute(
            [
                'task_type_name' => $d->task_type_name
            ]
        );
        $task_type_exists = $s->fetch();

        if ($task_type_exists != false) {
            // On retourne une erreur
            $this->logError(
                'Subject task type already exists', 0
            );
        } else {
            // On récupère l'id max du type de tache
            $s = $this->PDO->prepare(
                'SELECT max(ref_task_type_id) from ref_task_type'
            );
            $s->execute();
            $max_task_id = (int)($s->fetch()?:[0])[0]+1;
            /*
            if ($max_task_id == 0) {
                $max_task_id = 1;
            }*/

            if ($d->order == 0) {
                // On récupère l'ordre max du type de tache
                $s = $this->PDO->prepare(
                    'SELECT max(`order`) from ref_task_type'
                );
                $s->execute();
                $max_task_order = (int)($s->fetch()?:[0])[0]+1;
                /*
                if ($max_task_order == 0) {
                    $max_task_order = 1;
                }
                */
            } else {
                $max_task_order = $d->order;
            }

            // Insertion du nouveau type de tache
            $s = $this->PDO->prepare(
                'INSERT into ref_task_type(ref_task_type_id, task_type_name, color, `order`, type_prefix, default_estimation, delay_finish_to_delivery, default_effort_by_day) 
                values(:task_id, :task_type_name, :task_color, :task_order, :type_prefix, :default_estimation, :delay_finish_to_delivery, :default_effort_by_day)'
            );
            $s->execute(
                [
                    'task_id' => $max_task_id,
                    'task_type_name' => $d->task_type_name,
                    'task_color' => $d->task_color,
                    'task_order' => $max_task_order,
                    'type_prefix' => $d->type_prefix,
                    'default_estimation' => $d->default_estimation,
                    'delay_finish_to_delivery' => $d->delay_finish_to_delivery,
                    'default_effort_by_day' => $d->default_effort_by_day
                ]
            );
        }

        return true;
    }

    /**
     * Ajout d'un status pour le type de tache choisi
     * 
     * @return true
     */
    function addSubjectTaskStatus()
    {
        $d = $this->checkParams(
            [
                'ref_task_type_id' => 'int',
                'ref_task_status_id' => 'int',
            ]
        );

        // On vérifie que le statut n'existe pas déjà pour le type de tache
        $s = $this->PDO->prepare(
            'SELECT * from ref_task_type_status where 
            ref_task_type_id=:ref_task_type_id and 
            ref_task_status_id=:ref_task_status_id'
        );
        $s->execute(
            [
                'ref_task_type_id' => $d->ref_task_type_id,
                'ref_task_status_id' => $d->ref_task_status_id
            ]
        );
        $task_status_exists = $s->fetch();

        if ($task_status_exists != false) {
            $this->logError(
                'Status already exists for this task', 0
            );
        } else {
            $s = $this->PDO->prepare(
                'INSERT into ref_task_type_status(ref_task_type_id, ref_task_status_id) values(:ref_task_type_id, :ref_task_status_id)'
            );
            $s->execute(
                [
                    'ref_task_type_id' => $d->ref_task_type_id,
                    'ref_task_status_id' => $d->ref_task_status_id
                ]
            );
        }

        return true;
    }

    /**
     * Ajout d'un type d'intervention
     * 
     * @return true
     */
    function addSubjectType()
    {
        $d = $this->checkParams(
            [
                'type_name' => 'string',
                'order' => 'int',
                'icon' => 'string'
            ]
        );

        // On vérifie que le type d'intervention n'existe pas déjà
        $s = $this->PDO->prepare(
            'SELECT * from ref_subject_type where type_name=:type_name'
        );
        $s->execute(
            [
                'type_name' => $d->type_name
            ]
        );
        $type_exists = $s->fetch();

        if ($type_exists != false) {
            // Le type existe déjà
            $this->logError(
                'Subject type already exists', 0
            );
        } else {
            // On récupère l'id max du nouveau type d'intervention
            $s = $this->PDO->prepare(
                'SELECT max(ref_subject_type_id) from ref_subject_type'
            );
            $s->execute();
            $max_subject_type_id = (int)($s->fetch()?:[0])[0]+1;
            /*
            if ($max_subject_type_id == 0) {
                $max_subject_type_id = 1;
            }
            */
            if ($d->order == 0) {
                // On récupère l'order du nouveau type d'intervention
                $s = $this->PDO->prepare(
                    'SELECT max(`order`) from ref_subject_type'
                );
                $s->execute();
                $max_subject_type_order = (int)($s->fetch()?:[0])[0]+1;
                /*
                if ($max_subject_type_order == 0) {
                    $max_subject_type_order = 1;
                }
                */
            } else {
                $max_subject_type_order = $d->order;
            }

            // Insertion du nouveau type d'intervention
            $s = $this->PDO->prepare(
                'INSERT into ref_subject_type(ref_subject_type_id, type_name,
                `order`,type_icon) values(:ref_subject_type_id, :type_name, :order,:type_icon)'
            );
            $s->execute(
                [
                    'ref_subject_type_id' => $max_subject_type_id,
                    'type_name' => $d->type_name,
                    'order' => $max_subject_type_order,
                    'type_icon' => $d->icon
                ]
            );
        }

        return true;
    }

    /**
     * Création d'un nouveau statut de tache
     * 
     * @return true
     */
    function addTaskStatus()
    {
        $d = $this->checkParams(
            [
                'task_status_name' => 'string',
                'color' => 'string',
                'order' => 'int',
                'icon_class' => 'string',
                'is_final' => 'string',
                'is_new' => 'string',
                'is_inprogress' => 'string'
            ]
        );

        // On vérifie que le statut de tache n'existe pas déjà
        $s = $this->PDO->prepare(
            'SELECT * from ref_task_status where task_status_name=:task_status_name'
        );
        $s->execute(
            [
                'task_status_name' => $d->task_status_name
            ]
        );
        $task_status_exists = $s->fetch();

        if ($task_status_exists != false) {
            // Le status existe déjà
            $this->logError(
                'Task status already exists', 0
            );
        } else {
            // On récupère l'id max pour le statut de la tache
            $s = $this->PDO->prepare(
                'SELECT max(ref_task_status_id) from ref_task_status'
            );
            $s->execute();
            $task_status_id = (int)($s->fetch()?:[0])[0]+1;
            /*
            if ($task_status_id == 0) {
                $task_status_id = 1;
            }
            */

            $task_order = 0;
            if ($d->order == 0) {
                // On récupère l'order max de task_status
                $s = $this->PDO->prepare(
                    'SELECT max(`order`) from ref_task_status'
                );
                $s->execute();
                $task_order = (int)($s->fetch()?:[0])[0]+1;
                /*
                if ($task_order == 0) {
                    $task_order = 1;
                }
                */
            } else {
                $task_order = $d->order;
            }

            // Insertion du nouveau statut de tache
            $s = $this->PDO->prepare(
                'INSERT into ref_task_status(ref_task_status_id, task_status_name, color, `order`, icon_class, is_final, is_new, is_inprogress) 
                values(:ref_task_status_id, :task_status_name, :color, :order, :icon_class, :is_final, :is_new, :is_inprogress)'
            );
            $s->execute(
                [
                    'ref_task_status_id' => $task_status_id,
                    'task_status_name' => $d->task_status_name,
                    'color' => $d->color,
                    'order' => $task_order,
                    'icon_class' => $d->icon_class,
                    'is_final' => $d->is_final,
                    'is_new' => $d->is_new,
                    'is_inprogress' => $d->is_inprogress
                ]
            );
        }

        return true;
    }

    /**
     * Création d'un nouveau statut des execrequest
     * 
     * @return true
     */
    function addexecrequestStatus()
    {
        $d = $this->checkParams(
            [
                'execrequest_status_name' => 'string',
                'color' => 'string',
                'order' => 'int',
                'is_final' => 'string',
                'is_new' => 'string',
                'is_inprogress' => 'string'
            ]
        );

        // On vérifie que le statut de tache n'existe pas déjà
        $s = $this->PDO->prepare(
            'SELECT * from ref_execrequest_status where execrequest_status_name=:execrequest_status_name'
        );
        $s->execute(
            [
                'execrequest_status_name' => $d->execrequest_status_name
            ]
        );
        $execrequest_status_exists = $s->fetch();

        if ($execrequest_status_exists != false) {
            // Le status existe déjà
            $this->logError(
                'execrequest status already exists', 0
            );
        } else {
            // On récupère l'id max pour le statut de la tache
            $s = $this->PDO->prepare(
                'SELECT max(ref_execrequest_status_id) from ref_execrequest_status'
            );
            $s->execute();
            $execrequest_status_id = (int)($s->fetch()?:[0])[0]+1;
            /*
            if ($task_status_id == 0) {
                $task_status_id = 1;
            }
            */

            $execrequest_order = 0;
            if ($d->order == 0) {
                // On récupère l'order max de task_status
                $s = $this->PDO->prepare(
                    'SELECT max(`order`) from ref_execrequest_status'
                );
                $s->execute();
                $execrequest_order = (int)($s->fetch()?:[0])[0]+1;
                /*
                if ($task_order == 0) {
                    $task_order = 1;
                }
                */
            } else {
                $execrequest_order = $d->order;
            }

            // Insertion du nouveau statut de tache
            $s = $this->PDO->prepare(
                'INSERT into ref_execrequest_status(ref_execrequest_status_id, execrequest_status_name, color, `order`,  is_final, is_new, is_inprogress) 
                values(:ref_execrequest_status_id, :execrequest_status_name, :color, :order, :is_final, :is_new, :is_inprogress)'
            );
            $s->execute(
                [
                    'ref_execrequest_status_id' => $execrequest_status_id,
                    'execrequest_status_name' => $d->execrequest_status_name,
                    'color' => $d->color,
                    'order' => $execrequest_order,
                    'is_final' => $d->is_final,
                    'is_new' => $d->is_new,
                    'is_inprogress' => $d->is_inprogress
                ]
            );
        }

        return true;
    }

    /**
     * Création d'une nouvelle intervention à partir de son titre
     * 
     * @return true
     */
    function createNewSubject()
    {
        $d = $this->checkParams(
            [
                'title' => 'html',
                'pool_id' => 'int'
            ]
        );
        $d = $this->initOptionalParams('delivery_id', 'int', '');
        $d = $this->initOptionalParams('type_id', 'int', '');
        $d = $this->initOptionalParams('priority_id', 'int', '');
        $d = $this->initOptionalParams('in_charge_id', 'int', '');
        $d = $this->initOptionalParams('status_id', 'int', '');
        $d = $this->initOptionalParams('description', 'html', '');
        $d = $this->initOptionalParams('ei_subject_external_id', 'string', '');

        $title = urldecode($d->title);
        $description = urldecode($d->description);

        // error_log($d->ei_subject_external_id);

        // $s = $this->PDO->prepare(
        //     'SELECT current_pool_id from ei_user where ei_user_id=
        //     :ei_user_id'
        // );
        // $s->execute(
        //     [
        //         'ei_user_id' => $this->user['ei_user_id']
        //     ]
        // );
        // $pool_id = (int)($s->fetch()?:[0])[0]; 

        // On récupère les valeurs par défaut de la pool de l'utilisateur
        $s = $this->PDO->prepare(
            'SELECT * from ei_pool where ei_pool_id=:ei_pool_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->pool_id
            ]
        );
        $current_pool = $s->fetch(PDO::FETCH_ASSOC);

        // Récupération de l'id max de l'intervention
        $s = $this->PDO->prepare(
            'SELECT max(ei_subject_id) from ei_subject'
        );
        $s->execute();
        $max_subject_id = (int)($s->fetch()?:[0])[0]+1; 
        /*
        if ($max_subject_id == 0) {
            $max_subject_id = 1;
        }
        */


        if ($d->delivery_id != 0) {
            $delivery_id = $d->delivery_id;
        } else {
            $delivery_id = $current_pool['default_delivery'];
        }

        if ($d->type_id != 0) {
            $type_id = $d->type_id;
        } else {
            $type_id = $current_pool['default_subject_type'];
        }

        if ($d->priority_id != 0) {
            $priority_id = $d->priority_id;
        } else {
            $priority_id = $current_pool['default_subject_priority'];
        }

        if ($d->status_id != 0) {
            $status_id = $d->status_id;
        } else {
            $status_id = $current_pool['default_subject_status'];
        }
        
        if ($d->in_charge_id) {
            $charge_id = $d->in_charge_id;
        } else {
            $charge_id = $this->user['ei_user_id'];
        }
        // Création de la nouvelle intervention
        $s = $this->PDO->prepare(
            'INSERT into ei_subject(ei_subject_id,ei_subject_external_id, ei_subject_version_id, 
            ei_pool_id, ei_pool_origin, ei_delivery_id, ref_subject_type_id,
            ref_subject_status_id, ref_subject_priority_id, title, 
            description,ei_subject_user_in_charge, creator_id, created_at) values(:ei_subject_id, :ei_subject_external_id,
            1, :ei_pool_id, :ei_pool_origin, :ei_delivery_id, :ref_subject_type_id,
            :ref_subject_status_id, :ref_subject_priority_id, :title, 
            :description,:in_charge_id, :creator_id, now())'
        );
        $s->execute(
            [
                'ei_subject_id' => $max_subject_id,
                'ei_subject_external_id' => $d->ei_subject_external_id,
                'ei_pool_id' => $current_pool['ei_pool_id'],
                'ei_pool_origin' => $current_pool['ei_pool_id'],
                'ei_delivery_id' => $delivery_id ,
                'ref_subject_type_id' => $type_id ,
                'ref_subject_status_id' => $status_id ,
                'ref_subject_priority_id' => $priority_id ,
                'title' => $title,
                'description' => $description,
                'creator_id' => $this->user['ei_user_id'],
                'in_charge_id' => $charge_id,
            ]
        );

        $s = $this->PDO->prepare(
            'INSERT INTO `ei_subject_patch` (`ei_subject_id`, `ei_subject_patch_version_id`, `title`, `description`, `ref_patch_status_id`, `ref_patch_type_id`, `created_by`, `created_at`) VALUES (:ei_subject_id, 1, "","", 1, 1, :creator_id, now())'
        );
        $s->execute(
            [
                'ei_subject_id' => $max_subject_id,
                'creator_id' => $this->user['ei_user_id'],
            ]
        );

        $action = 'Subject was created by '.$this->user['username'].' at '.
            date('Y-m-d H:i:s');

        /* Création des taches liées au type d'intervention par défaut de la pool
        si elles sont en created by default*/

        // On récupère la liste des taches qui doivent être créées avec le type 
        // d'intervention
        $s = $this->PDO->prepare(
            'SELECT * from ei_pool_subject_type_task_type where ref_subject_type_id=
            :ref_subject_type_id and ei_pool_id=:ei_pool_id and created_by_default="Y"'
        );
        $s->execute(
            [
                'ref_subject_type_id' => $current_pool['default_subject_type'],
                'ei_pool_id' => $d->pool_id
            ]
        );
        $task_list = $s->fetchAll(PDO::FETCH_ASSOC);

        foreach ($task_list as $index => $value) {
            // On récupère l'id max de la nouvelle tache
            $s = $this->PDO->prepare(
                'SELECT max(ei_task_id) from ei_task'
            );
            $s->execute();
            $max_task_id = (int)($s->fetch()?:[0])[0]+1; 
            /*
            if ($max_task_id == 0) {
                $max_task_id = 1;
            }
            */

            // On insère cette nouvelle tache
            $s = $this->PDO->prepare(
                'INSERT into ei_task(ei_task_id, ref_task_type_id, 
                ref_task_status_id, title, description, ei_user_id, 
                estimation, final_cost, creator_id,created_at,expected_end,expected_start) values(:ei_task_id, 
                :ref_task_type_id, :ref_task_status_id, :title, "", 
                :user_id, :estimation, 0,0,NOW(),0000-00-00,0000-00-00)'
            );
            $s->execute(
                [
                    'ei_task_id' => $max_task_id,
                    'ref_task_type_id' => $value['ref_task_type_id'],
                    'ref_task_status_id' => 
                    $value['default_task_status_id'],
                    'title' => $value['default_title'],
                    'user_id' => $value['default_user_id'],
                    'estimation' => $value['default_estimation']
                ]
            );

            // Récupération de l'id max du lien de tache
            $s = $this->PDO->prepare(
                'SELECT max(ei_task_link_id) from ei_task_link'
            );
            $s->execute();
            $max_task_link_id = (int)($s->fetch()?:[0])[0]+1; 
            /*
            if ($max_task_link_id == 0) {
                $max_task_link_id = 1;
            }
            */

            // On insère le lien entre l'intervention et la tache
            $s = $this->PDO->prepare(
                'INSERT into ei_task_link(ei_task_id, ei_task_link_id, 
                ref_object_type_id, object_id) values(:ei_task_id, 
                :ei_task_link_id, "SUBJECT", :object_id)'
            );
            $s->execute(
                [
                    'ei_task_id' => $max_task_id,
                    'ei_task_link_id' => $max_task_link_id,
                    'object_id' => $max_subject_id
                ]
            );

            $this->callClass(
                "Task", 
                "addFlagToTask", 
                [
                    'ei_task_id' => $max_task_id,
                    'ref_flag_id' => 1,

                ]
            );
        }
        $this->setData($max_subject_id);
        return true;
    }

    /**
     * Suppression de la permission d'une application module mode
     * 
     * @return true
     */
    function deletePermissionForApplicationModuleMode()
    {
        $d = $this->checkParams(
            [
                'ei_api_application_id' => 'string',
                'ei_api_application_module_id' => 'string',
                'ei_api_application_module_mode_id' => 'string',
                'ei_api_permission_id' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            'DELETE from ei_api_application_module_mode_permission where 
            ei_api_application_id=:ei_api_application_id and 
            ei_api_application_module_id=:ei_api_application_module_id and 
            ei_api_application_module_mode_id=:ei_api_application_module_mode_id 
            and ei_api_permission_id=:ei_api_permission_id'
        );
        $s->execute(
            [
                'ei_api_application_id' => $d->ei_api_application_id,
                'ei_api_application_module_id' => $d->ei_api_application_module_id,
                'ei_api_application_module_mode_id' => 
                $d->ei_api_application_module_mode_id,
                'ei_api_permission_id' => $d->ei_api_permission_id
            ]
        );

        return true;
    }

    /**
     * Suppression de la permission d'un rôle
     * 
     * @return true
     */
    function deletePermissionForRole()
    {
        $d = $this->checkParams(
            [
                'ei_api_role_id' => 'string',
                'ei_api_permission_id' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            'DELETE from ei_api_role_permission where ei_api_role_id=:ei_api_role_id
            and ei_api_permission_id=:ei_api_permission_id'
        );
        $s->execute(
            [
                'ei_api_role_id' => $d->ei_api_role_id,
                'ei_api_permission_id' => $d->ei_api_permission_id
            ]
        );

        return true;
    }

    /**
     * Suppression d'une priorité d'intervention de la pool
     * 
     * @return true
     */
    function deletePoolSubjectPriority()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_priority_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'DELETE from ei_pool_subject_priority where ei_pool_id=:ei_pool_id and 
            ref_subject_priority_id=:ref_subject_priority_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_priority_id' => $d->ref_subject_priority_id
            ]
        );

        return true;
    }

    /**
     * Suppression d'un status du type d'intervention de la pool
     * 
     * @return true
     */
    function deletePoolSubjectStatus()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int',
                'ref_subject_status_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'DELETE from ei_pool_subject_status where ei_pool_id=:ei_pool_id
            and ref_subject_type_id=:ref_subject_type_id and 
            ref_subject_status_id=:ref_subject_status_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ref_subject_status_id' => $d->ref_subject_status_id
            ]
        );

        return true;
    }

    /**
     * Suppression d'un type d'intervention de la pool
     * 
     * @return true
     */
    function deletePoolSubjectType()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'DELETE from ei_pool_subject_type where ei_pool_id=:ei_pool_id and 
            ref_subject_type_id=:ref_subject_type_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id
            ]
        );

        return true;
    }

    /**
     * Suppression d'un type de tache pour le type d'intervention de la pool
     * 
     * @return true
     */
    function deletePoolSubjectTypeTaskType()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int',
                'ref_task_type_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'DELETE from ei_pool_subject_type_task_type where ei_pool_id=:ei_pool_id 
            and ref_subject_type_id=:ref_subject_type_id and 
            ref_task_type_id=:ref_task_type_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ref_task_type_id' => $d->ref_task_type_id
            ]
        );
        
        return true;
    }

    /**
     * Suppression d'un scénario de la campagne d'intervention
     * 
     * @return true
     */
    function deleteScenarioInSubjectCampaign()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_subject_campaign_id' => 'int',
                'ei_subject_campaign_scenario_id' => 'int',
                'ei_scenario_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'DELETE from ei_subject_campaign_scenario where ei_subject_id=:ei_subject_id and ei_subject_campaign_id=:ei_subject_campaign_id and 
            ei_subject_campaign_scenario_id=:ei_subject_campaign_scenario_id and ei_scenario_id=:ei_scenario_id'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_subject_campaign_id' => $d->ei_subject_campaign_id,
                'ei_subject_campaign_scenario_id' => $d->ei_subject_campaign_scenario_id,
                'ei_scenario_id' => $d->ei_scenario_id
            ]
        );

        return true;
    }

    /**
     * Récupération de la liste des actions liées à la library choisie
     * 
     * @return true
     */
    function getActionListByLibrary() 
    {
        $d = $this->checkParams(
            [
                'ei_api_library_id' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT ei_api_library_action_id, description, type, root_record,
            code_file, code_function, comment, json_sample FROM 
            ei_api_library_action WHERE ei_api_library_id=
            :ei_api_library_id'
        );
        $s->execute(
            [
                'ei_api_library_id' => $d->ei_api_library_id
            ]
        );
        $action_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($action_list);

        return true;
    }

    /**
     * Récupération de la liste des applications module mode
     * 
     * @return array
     */
    function getApplicationModuleModeList()
    {
        $s = $this->PDO->prepare(
            'SELECT ei_api_application_id, ei_api_application_module_id, 
            ei_api_application_module_mode_id from ei_api_application_module_mode'
        );
        $s->execute();
        $list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($list);

        return true;
    }

    /**
     * Récupération de la plannification des campaigns
     * 
     * @return array
     */
    function getCampaignPlane()
    {
        $d = $this->checkParams(
            [
                'ei_campaign_id' => 'int'
            ]
        );
        $s = $this->PDO->prepare(
            'SELECT  etdv.task_status_is_final,
                ecp.*,
                eu.username,
                ee.name as env_name,
                etdv.task_type_name,
                etdv.task_title,
                etdv.task_description,
                etdv.task_status_icon_class,
                etdv.user_picture_path
            FROM
                ei_campaign_plane ecp
                    LEFT OUTER JOIN
                ei_user eu ON eu.ei_user_id = ecp.ei_user
                    LEFT OUTER JOIN
                ei_environment ee ON ee.ei_environment_id = ecp.ei_environment_id
                    LEFT OUTER JOIN
                ei_task_detail_vw etdv ON ecp.ei_task_id = etdv.ei_task_id 
                AND etdv.connected_user_id = :connected_user_id
            WHERE
                ecp.ei_campaign_id = :ei_campaign_id
                
            ;'
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->ei_campaign_id,
                'connected_user_id' => $this->user['ei_user_id']
            ]
        );
        $campaignPlaneList = $s->fetchAll(PDO::FETCH_ASSOC);

        foreach ($campaignPlaneList as $plane => $value) {
            $task_open = $this->callClass(
                "Core", 
                "getCampaignPlaneTaskStatus", 
                [
                    'ei_campaign_id' => $d->ei_campaign_id,
                    'ei_campaign_plane_id' =>$value['ei_campaign_plane_id'],
                ]
            );
            $task_open_data = $task_open->getData();
            $test = array_merge($value, $task_open_data);
            $campaignPlaneList[$plane] = $test;
        }
        

        $this->setData($campaignPlaneList);

        return true;
    }

    /**
     * Récupération de toutes les task du campaign plane
     * 
     * @return array
     */
    function getCampaignPlaneTaskStatus()
    {
        $d = $this->checkParams(
            [
                'ei_campaign_id' => 'int',
                'ei_campaign_plane_id' => 'int'
            ]
        );
        $s = $this->PDO->prepare(
            "select count(main_task) as task_open from ((SELECT etdv.task_status_is_final as main_task
            FROM
                ei_campaign_plane ecp
                    LEFT OUTER JOIN
                ei_user eu ON eu.ei_user_id = ecp.ei_user
                    LEFT OUTER JOIN
                ei_environment ee ON ee.ei_environment_id = ecp.ei_environment_id
                    LEFT OUTER JOIN
                ei_task_detail_vw etdv ON ecp.ei_task_id = etdv.ei_task_id
                AND etdv.connected_user_id = :connected_user_id
            WHERE
                ecp.ei_campaign_id = :ei_campaign_id
            ) union all(
            SELECT 	etdv.task_status_is_final as step_task
            FROM
                ei_campaign_step_plane ecp
                    LEFT OUTER JOIN
                ei_task_detail_vw etdv ON ecp.ei_task_id = etdv.ei_task_id AND etdv.connected_user_id = :connected_user_id
                left outer join ei_execution ee on ee.ei_execution_id=ecp.ei_execution_id
                LEFT OUTER JOIN
                ei_user eu ON eu.ei_user_id = ee.ei_user_id
                LEFT OUTER JOIN
                ei_environment eev ON eev.ei_environment_id = ee.ei_environment_id
                left outer join ei_campaign_step ecs on ecs.ei_campaign_id=ecp.ei_campaign_id and ecs.ei_campaign_step_id=ecp.ei_campaign_step_id
            WHERE
                ecp.ei_campaign_plane_id = :ei_campaign_plane_id and ecp.ei_campaign_id= :ei_campaign_id)) var where main_task = 'N'"
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->ei_campaign_id,
                'ei_campaign_plane_id' => $d->ei_campaign_plane_id,
                'connected_user_id' => $this->user['ei_user_id']
            ]
        );
        $campaignPlaneList = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($campaignPlaneList);

        return true;
    }

    /**
     * Update Close Date Campaign Plane
     * 
     * @return array
     */
    function updateCampaignPlaneCloseDate()
    {
        $d = $this->checkParams(
            [
                'ei_campaign_id' => 'int',
                'ei_campaign_plane_id' => 'int'
            ]
        );
        $s = $this->PDO->prepare(
            "UPDATE `ei_campaign_plane` SET `ei_campaign_plane_close_date`=now() WHERE `ei_campaign_plane_id`=:ei_campaign_plane_id and ei_campaign_id=:ei_campaign_id;"
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->ei_campaign_id,
                'ei_campaign_plane_id' => $d->ei_campaign_plane_id
            ]
        );

        return true;
    }


    /**
     * Crée campaign plane
     * 
     * @return array
     */
    function createCampaignPlane()
    {
        $d = $this->checkParams(
            [
                'ei_campaign_id' => 'int',
                'ei_task_id' => 'int',
                'ei_env_id' => 'int',
                'ei_campaign_plane_label' => 'string',
                'start_date' => 'string',
                'end_date' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT max(ei_campaign_plane_id)+1 as max_plane_id FROM ei_campaign_plane where ei_campaign_id=:ei_campaign_id'
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->ei_campaign_id,
            ]
        );
        $maxCampaignPlaneId = $s->fetch(PDO::FETCH_ASSOC);

        $s = $this->PDO->prepare(
            'SELECT max(ei_campaign_version_id) as max_version FROM ei_campaign_version where ei_campaign_id=:ei_campaign_id;'
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->ei_campaign_id,
            ]
        );
        $maxCampaignVersionId = $s->fetch(PDO::FETCH_ASSOC);

        $s = $this->PDO->prepare(
            'INSERT INTO `ei_campaign_plane` (
            `ei_campaign_id`, `ei_campaign_plane_id`, `ei_task_id`, `ei_user`, `ei_environment_id`, `ei_campaign_version_id`,
            `ei_campaign_plane_expected_start_date`, `ei_campaign_plane_expected_end_date`, `ei_campaign_plane_label`
            
            ) VALUES (:ei_campaign_id, :max_plane_id, :ei_task_id,:ei_user_id, :ei_env_id, :max_version_id , :start_date, :end_date, :ei_campaign_plane_label);'
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->ei_campaign_id,
                'ei_task_id' => $d->ei_task_id,
                'start_date' => $d->start_date,
                'end_date' => $d->end_date,
                'max_plane_id' => $maxCampaignPlaneId['max_plane_id'],
                'max_version_id' => $maxCampaignVersionId['max_version'],
                'ei_user_id' => $this->user['ei_user_id'],
                'ei_env_id' => $d->ei_env_id,
                'ei_campaign_plane_label' => $d->ei_campaign_plane_label
            ]
        );

        return true;
    }

    /**
     * Crée campaign Step plane
     * 
     * @return array
     */
    function createCampaignStepPlane()
    {
        $d = $this->checkParams(
            [
                'ei_campaign_id' => 'int',
                'ei_task_id' => 'int',
                'ei_execution_id' => 'int',
                'ei_campaign_step_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT max(ei_campaign_plane_id) as max_plane_id FROM ei_campaign_plane where ei_campaign_id=:ei_campaign_id'
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->ei_campaign_id,
            ]
        );
        $maxCampaignPlaneId = $s->fetch(PDO::FETCH_ASSOC);
        $s = $this->PDO->prepare(
            'INSERT INTO `ei_campaign_step_plane` (`ei_campaign_id`, `ei_campaign_plane_id`, `ei_campaign_step_id`, `ei_task_id`, `ei_execution_id`) 
            VALUES (:ei_campaign_id, :ei_campaign_plane_id, :ei_campaign_step_id, :ei_task_id, :ei_execution_id);'
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->ei_campaign_id,
                'ei_task_id' => $d->ei_task_id,
                'ei_execution_id' =>$d->ei_execution_id,
                'ei_campaign_plane_id' => $maxCampaignPlaneId['max_plane_id'],
                'ei_campaign_step_id' => $d->ei_campaign_step_id
            ]
        );

        return true;
    }

    /**
     * Récupération de la plannification des campaigns step
     * 
     * @return array
     */
    function getCampaignStepPlane()
    {
        $d = $this->checkParams(
            [
                'ei_campaign_id' => 'int',
                'ei_campaign_plane_id' => 'int'
            ]
        );
        $s = $this->PDO->prepare(
            'SELECT 
            ecs.*,
                ecp.*,
                etdv.task_type_name,
                etdv.task_title,
                etdv.task_description,
                etdv.task_status_icon_class,
                etdv.user_picture_path,
                ee.status,
                ee.ei_environment_id, 
                ee.ei_user_id,
                ee.expected_date,
                eu.username,
                eev.name as env_name
            FROM
                ei_campaign_step_plane ecp
                    LEFT OUTER JOIN
                ei_task_detail_vw etdv ON ecp.ei_task_id = etdv.ei_task_id AND etdv.connected_user_id = :connected_user_id
                left outer join ei_execution ee on ee.ei_execution_id=ecp.ei_execution_id
                LEFT OUTER JOIN
                ei_user eu ON eu.ei_user_id = ee.ei_user_id
                LEFT OUTER JOIN
                ei_environment eev ON eev.ei_environment_id = ee.ei_environment_id
                left outer join ei_campaign_step ecs on ecs.ei_campaign_id=ecp.ei_campaign_id and ecs.ei_campaign_step_id=ecp.ei_campaign_step_id
            WHERE
                ecp.ei_campaign_plane_id = :ei_campaign_plane_id and ecp.ei_campaign_id= :ei_campaign_id;'
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->ei_campaign_id,
                'ei_campaign_plane_id' => $d->ei_campaign_plane_id,
                'connected_user_id' => $this->user['ei_user_id']
            ]
        );
        $campaignStepPlaneList = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($campaignStepPlaneList);

        return true;
    }

    /**
     * Récupération de la liste des livraisons
     * 
     * @return array
     */
    function getDeliveryList()
    {
        $s = $this->PDO->prepare(
            "SELECT distinct
                    d.ei_delivery_id,
                    d.delivery_name,
                    d.delivery_date,
                    d.ref_delivery_type_status_id,
                    rds.ref_delivery_type_id,
                    rds.ref_delivery_type_status_id,
                    rds.delivery_status_name,
                    rds.color,
                    rds.delivery_order,
                    
                    CASE
                        WHEN ep.ei_pool_id != 0 
                        THEN 1
                        ELSE 0
                END as deliveryInDefaultPool, 
                case when (SELECT 
                    count(1) 
                FROM
                    ei_subject es,
                    ref_subject_status rss
                WHERE
                    es.ei_subject_version_id = (SELECT 
                            MAX(s2.ei_subject_version_id)
                                FROM
                                    ei_subject s2
                                WHERE
                            s2.ei_subject_id = es.ei_subject_id)
                            
                        AND es.ei_delivery_id = d.ei_delivery_id
                        AND rss.ref_subject_status_id = es.ref_subject_status_id
                        AND ( rss.is_final <> 'Y'))  > 0 then 1 else 0 END as deliverySubjectStatus 
                
                FROM
                    ei_delivery d 
                    left outer join ref_delivery_status rds on d.ref_delivery_type_status_id = rds.ref_delivery_type_status_id
                    left outer join ei_pool ep on default_delivery=d.ei_delivery_id
                ORDER BY d.delivery_date DESC;"
        );
        $s->execute();
        $delivery_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($delivery_list);

        return true;
    }

    /**
     * Récupération de la liste des livraisons sur un subject
     * 
     * @return array
     */
    function getDeliveryListForSubject()
    {
        $s = $this->PDO->prepare(
            "SELECT d.ei_delivery_id, d.delivery_name, d.delivery_date, d.ref_delivery_type_status_id, rds.ref_delivery_type_id, 
            rds.ref_delivery_type_status_id, rds.delivery_status_name, rds.color, rds.delivery_order FROM ei_delivery d, ref_delivery_status rds 
            where d.ref_delivery_type_status_id=rds.ref_delivery_type_status_id and rds.is_final = 'N' order by d.delivery_date desc"
        );
        $s->execute();
        $delivery_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($delivery_list);

        return true;
    }

    /**
     * Récupération de la liste des library action en fonction de l'application
     * module mode sélectionnée
     * 
     * @return array
     */
    function getLibraryActionListByApplicationModuleMode()
    {
        $d = $this->checkParams(
            [
                'ei_api_application_id' => 'string',
                'ei_api_application_module_id' => 'string',
                'ei_api_application_module_mode_id' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT ei_api_library_id, ei_api_library_action_id from 
            ei_api_application_module_mode_library_action where 
            ei_api_application_id=:ei_api_application_id and 
            ei_api_application_module_id=:ei_api_application_module_id and 
            ei_api_application_module_mode_id=:ei_api_application_module_mode_id'
        );
        $s->execute(
            [
                'ei_api_application_id' => $d->ei_api_application_id,
                'ei_api_application_module_id' => $d->ei_api_application_module_id,
                'ei_api_application_module_mode_id' => 
                $d->ei_api_application_module_mode_id
            ]
        );
        $libray_action_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($libray_action_list);

        return true;
    }

    /**
     * Récupération de la liste des library de la base
     * 
     * @return array
     */
    function getLibraryList()
    {
        $s = $this->PDO->prepare(
            'SELECT ei_api_library_id, description from ei_api_library'
        );
        $s->execute();
        $library_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($library_list);

        return true;
    }

    /**
     * Récupération de la valeur d'un paramètre par rapport à l'utilisateur et 
     * à l'environnement courant
     * 
     * @return array
     */
    function getParamValue()
    {
        $d = $this->checkParams(
            [
                'param_nagfdffme' => 'string'
            ]
        );

        // Récupération de l'id du paramètre
        $s = $this->PDO->prepare(
            'SELECT ref_param_id from ref_param where name=:name'
        );
        $s->execute(
            [
                'name' => $d->param_name
            ]
        );
        $param_id = (int)($s->fetch()?:[0])[0]; 

        // Si la valeur se trouve dans ei_user_environment_param on la récupère de là
        // L'utilisateur a surchargé la valeur
        $s = $this->PDO->prepare(
            'SELECT value from ei_user_environment_param where 
            ref_param_id=:ref_param_id and ei_user_id=:ei_user_id 
            and ei_environment_id=:ei_environment_id'
        );
        $s->execute(
            [
                'ref_param_id' => $param_id,
                'ei_user_id' => $this->user['ei_user_id'],
                'ei_environment_id' => $this->user['current_environment_id']
            ]
        );
        $param_value = $s->fetch(PDO::FETCH_ASSOC);

        if ($param_value == false) {
            // L'utilisateur n'a pas surchargé cette valeur, on la récupère donc dans
            // ei_environment_param
            $s = $this->PDO->prepare(
                'SELECT value from ei_environment_param where 
                ref_param_id=:ref_param_id and ei_environment_id=
                :ei_environment_id'
            );
            $s->execute(
                [
                    'ref_param_id' => $param_id,
                    'ei_environment_id' => $this->user['current_environment_id']
                ]
            );
            $param_value = $s->fetch(PDO::FETCH_ASSOC);
        }

        $this->setData($param_value);

        return true;
    }

    /**
     * Récupération de la liste des permissions existantes
     * 
     * @return true
     */
    function getPermissionList()
    {
        $s = $this->PDO->prepare(
            'SELECT ei_api_permission_id from ei_api_permission'
        );
        $s->execute();
        $permission_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($permission_list);

        return true;
    }

    /**
     * Récupération de la liste des permissions liées à l'application module mode
     * sélectionnée
     * 
     * @return array
     */
    function getPermissionListByApplicationModuleMode()
    {
        $d = $this->checkParams(
            [
                'ei_api_application_id' => 'string',
                'ei_api_application_module_id' => 'string',
                'ei_api_application_module_mode_id' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT ei_api_permission_id from 
            ei_api_application_module_mode_permission where 
            ei_api_application_id=:ei_api_application_id and 
            ei_api_application_module_id=:ei_api_application_module_id and 
            ei_api_application_module_mode_id=:ei_api_application_module_mode_id'
        );
        $s->execute(
            [
                'ei_api_application_id' => $d->ei_api_application_id,
                'ei_api_application_module_id' => $d->ei_api_application_module_id,
                'ei_api_application_module_mode_id' => 
                $d->ei_api_application_module_mode_id
            ]
        );
        $permission_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($permission_list);

        return true;
    }

    /**
     * Récupération de la liste des permissions liées au role choisi
     * 
     * @return array
     */
    function getPermissionListByRole()
    {
        $d = $this->checkParams(
            [
                'ei_api_role_id' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT ei_api_permission_id from ei_api_role_permission where 
            ei_api_role_id=:ei_api_role_id'
        );
        $s->execute(
            [
                'ei_api_role_id' => $d->ei_api_role_id
            ]
        );
        $permission_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($permission_list);

        return true;
    }

    /**
     * Récupération des informations de la pool par son id
     * 
     * @return true
     */
    function getPoolById()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int'
            ]
        );

        // Permet de récupérer les noms des types, status etc liés à la pool
        $s = $this->PDO->prepare(
            'SELECT p.ei_pool_id, p.pool_name, p.pool_color, st.ref_subject_type_id, st.type_name, 
            ss.ref_subject_status_id, ss.status_name, ss.color as status_color, 
            sp.ref_subject_priority_id, sp.priority_name, sp.color as priority_color,
            d.ei_delivery_id, d.delivery_name, p.order 
            from ei_pool p 
            left outer join ref_subject_type st 
            on p.default_subject_type=st.ref_subject_type_id
            left outer join ref_subject_status ss
            on p.default_subject_status=ss.ref_subject_status_id
            left outer join ref_subject_priority sp
            on p.default_subject_priority=sp.ref_subject_priority_id 
            left outer join ei_delivery d
            on p.default_delivery=d.ei_delivery_id
            where ei_pool_id=:ei_pool_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id
            ]
        );
        $pool = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($pool);

        return true;
    }

    /**
     * Récupération de la liste des pools existants (utilisés pour les interventions)
     * 
     * @return array
     */
    function getPoolList()
    {
        $s = $this->PDO->prepare(
            'SELECT * from ei_pool order by `order` asc'
        );
        $s->execute();
        $pool_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($pool_list);

        return true;
    }

    /**
     * Récupération de la liste des priorités d'intervention pour la pool
     * 
     * @return true
     */
    function getPoolSubjectPriorityList()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT sp.ref_subject_priority_id, sp.priority_name, sp.color,  sp.priority_picto, 
            psp.order from ei_pool_subject_priority psp, ref_subject_priority sp where 
            psp.ref_subject_priority_id=sp.ref_subject_priority_id and 
            psp.ei_pool_id=:ei_pool_id order by psp.order asc'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id
            ]
        );
        $pool_subject_priority_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($pool_subject_priority_list);

        return true;
    }

    /**
     * Récupération des informations du type d'intervention par son id et celui
     * de la pool
     * 
     * @return true
     */
    function getPoolSubjectTypeById()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT st.* from ei_pool_subject_type pst, ref_subject_type st where 
            pst.ref_subject_type_id=st.ref_subject_type_id and 
            pst.ei_pool_id=:ei_pool_id and pst.ref_subject_type_id=:ref_subject_type_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id
            ]
        );
        $pool_subject_type = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($pool_subject_type);

        return true;
    }
    
    /**
     * Récupération de la liste des status du type d'intervention de la pool
     * 
     * @return true
     */
    function getPoolSubjectStatusListByPoolSubjectTypeId()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT ss.ref_subject_status_id, ss.color,ss.status_icon, ss.status_name,
            pss.status_name as status_name_override,pss.is_final, pss.order from ei_pool_subject_status pss, 
            ref_subject_status ss where pss.ref_subject_status_id=ss.ref_subject_status_id 
            and pss.ei_pool_id=:ei_pool_id and pss.ref_subject_type_id=
            :ref_subject_type_id order by pss.order asc'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id
            ]
        );
        $pool_subject_status_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($pool_subject_status_list);

        return true;
    }

    // /**
    //  * Récupération de la liste de types de campagne pour le type d'intervention et la pool
    //  * 
    //  * @return array
    //  */
    // function getPoolSubjectTypeCampaignTypeList()
    // {
    //     $d = $this->checkParams(
    //         [
    //             'ei_pool_id' => 'int',
    //             'ref_subject_type_id' => 'int'
    //         ]
    //     );

    //     $s = $this->PDO->prepare(
    //         'SELECT * from ei_subject_campaigntype where ei_pool_id=:ei_pool_id and ref_subject_type_id=:ref_subject_type_id'
    //     );
    //     $s->execute(
    //         [
    //             'ei_pool_id' => $d->ei_pool_id,
    //             'ref_subject_type_id' => $d->ref_subject_type_id
    //         ]
    //     );
    //     $campaign_list = $s->fetchAll(PDO::FETCH_ASSOC);

    //     $this->setData($campaign_list);

    //     return true;
    // }

    /**
     * Récupération de la liste des types d'intervention pour la pool
     * 
     * @return true
     */
    function getPoolSubjectTypeList()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT st.ref_subject_type_id, st.type_name, st.type_icon, pst.order 
            from ei_pool_subject_type pst, ref_subject_type st where 
            pst.ref_subject_type_id=st.ref_subject_type_id and 
            pst.ei_pool_id=:ei_pool_id order by pst.order asc'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id
            ]
        );
        $pool_subject_type_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($pool_subject_type_list);

        return true;
    }

    /**
     * Récupération de la liste des types de taches pour le type d'intervention 
     * de la pool
     * 
     * @return array
     */
    function getPoolSubjectTypeTaskTypeList()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT psttt.ei_pool_id, psttt.ref_subject_type_id, psttt.ref_task_type_id, 
            rtt.task_type_name, rtt.color, psttt.created_by_default, 
            psttt.default_estimation, psttt.default_user_id, u.username, 
            psttt.default_task_status_id, rts.task_status_name, 
            rts.color as status_color, psttt.order, psttt.default_title
            from ei_pool_subject_type_task_type psttt 
            left outer join ref_task_type rtt
            on psttt.ref_task_type_id=rtt.ref_task_type_id
            left outer join ei_user u
            on psttt.default_user_id=u.ei_user_id
            left outer join ei_pool_subject_type_task_type_status psttts
            on psttt.ref_task_type_id=psttts.ref_task_type_id and
            psttt.default_task_status_id=psttts.ref_task_status_id and psttt.ei_pool_id=psttts.ei_pool_id and
            psttt.ref_subject_type_id=psttts.ref_subject_type_id
            left outer join ref_task_status rts on psttts.ref_task_status_id=rts.ref_task_status_id
            where psttt.ei_pool_id=:ei_pool_id and psttt.ref_subject_type_id=
            :ref_subject_type_id order by psttt.`order` asc'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id
            ]
        );
        $pool_subject_type_task_type_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($pool_subject_type_task_type_list);

        return true;
    }

    /**
     * Récupération de la liste des rôles qui existent
     * 
     * @return array
     */
    function getRoleList()
    {
        $s = $this->PDO->prepare(
            'SELECT ei_api_role_id from ei_api_role'
        );
        $s->execute();
        $role_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($role_list);

        return true;
    }

    /**
     * Récupération des infso du projet
     * 
     * @return array
     */
    function getProjectInf()
    {
        $s = $this->PDO->prepare(
            'SELECT * FROM ref_install;'
        );
        $s->execute();
        $role_list = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($role_list);

        return true;
    }

    /**
     * Récupération de la liste des rôles par rapport à l'utilisateur sélectionné
     * 
     * @return array
     */
    function getRoleListByUser()
    {
        $d = $this->checkParams(
            [
                'ei_user_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT ei_api_role_id from ei_api_role_user where ei_user_id=
            :ei_user_id'
        );
        $s->execute(
            [
                'ei_user_id' => $d->ei_user_id
            ]
        );
        $role_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($role_list);

        return true;
    }

    /**
     * Récupération des informations de l'intervention avec son id (pour la page 
     * de propriétés)
     * 
     * @return array
     */
    function getSubjectById()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );

        // On récupère par défaut les informations de la dernière version 
        // de l'intervention
        $s = $this->PDO->prepare(
            "SELECT u3.subject_user_pin,
                s.ei_subject_id,
                s.title,
                p.ei_pool_id,
                p.pool_name,
                d.ei_delivery_id,
                d.delivery_name,
                rds.is_final AS delivery_final,
                st.ref_subject_type_id,
                st.type_name,
                st.type_icon,
                ss.ref_subject_status_id,
                ss.status_name,
                ss.color AS status_color,
                ss.is_final,
                ss.status_icon,
                sp.ref_subject_priority_id,
                sp.priority_name,
                sp.color AS priority_color,
                sp.priority_picto,
                u.username,
                u.picture_path,
                s.created_at,
                s.description,
                esp.title AS patch_title,
                esp.description AS patch_description,
                IFNULL(esp.ref_patch_status_id, 1) AS patch_status,
                IFNULL(esp.ref_patch_type_id, 1) AS patch_type,
                IFNULL(rpt.type_name,'internal')   AS patch_type_name,
                IFNULL(rps.status_name,'draft')  AS patch_status_name,
                s.ei_subject_user_in_charge,
                u2.picture_path AS in_charge_picture_path,
                u2.username AS in_charge_username,
                s.ei_subject_external_id,
                esr.risk_exec,
                esr.risk_exec_ok,
                esr.risk_exec_ko,
                esr.total_risk
            FROM ei_subject s
            LEFT OUTER JOIN ei_pool p ON s.ei_pool_id=p.ei_pool_id
            LEFT OUTER JOIN ei_delivery d ON s.ei_delivery_id=d.ei_delivery_id
            LEFT OUTER JOIN ref_delivery_status rds ON d.ref_delivery_type_status_id=rds.ref_delivery_type_status_id
            LEFT OUTER JOIN ei_subject_patch esp ON esp.ei_subject_id=s.ei_subject_id and esp.ei_subject_patch_version_id=(select max(esp2.ei_subject_patch_version_id) from ei_subject_patch esp2 where esp2.ei_subject_id=esp.ei_subject_id)
            LEFT OUTER JOIN ref_patch_type rpt ON rpt.ref_patch_type_id = esp.ref_patch_type_id
			LEFT OUTER JOIN ref_patch_status rps ON rps.ref_patch_status_id = esp.ref_patch_status_id
            AND
            (SELECT max(ei_subject_patch_version_id)
            FROM ei_subject_patch
            WHERE ei_subject_id=s.ei_subject_id)=esp.ei_subject_patch_version_id
            LEFT OUTER JOIN ref_subject_type st ON s.ref_subject_type_id=st.ref_subject_type_id
            LEFT OUTER JOIN ref_subject_status ss ON s.ref_subject_status_id=ss.ref_subject_status_id
            LEFT OUTER JOIN ref_subject_priority sp ON s.ref_subject_priority_id=sp.ref_subject_priority_id
            LEFT OUTER JOIN ei_user u ON s.creator_id=u.ei_user_id
            LEFT OUTER JOIN ei_user u2 ON s.ei_subject_user_in_charge=u2.ei_user_id
            LEFT OUTER JOIN
            (SELECT current_subject_id,
                    concat('[', GROUP_CONCAT('{\"username\":\"', username, '\",\"picture_path\":\"', picture_path, '\"}' SEPARATOR ', '), ']') AS subject_user_pin
            FROM ei_user
            GROUP BY current_subject_id) u3 ON u3.current_subject_id= s.ei_subject_id
            LEFT OUTER JOIN
            (SELECT esr.ei_subject_id,
                    SUM(
                            (SELECT COUNT(1)
                            FROM ei_function_stat efs
                            WHERE efs.nb_ok > 1
                            AND efs.ei_function_id = esr.ei_function_id
                            AND ei_iteration_id=:ei_iteration_id)) AS risk_exec,
                    COUNT(*) AS total_risk,
                    SUM(
                            (SELECT COUNT(1)
                            FROM ei_function_stat efs
                            WHERE efs.last_status = 'ok'
                            AND efs.ei_function_id = esr.ei_function_id
                            AND ei_iteration_id = :ei_iteration_id)) AS risk_exec_ok,
                    SUM(
                            (SELECT COUNT(1)
                            FROM ei_function_stat efs
                            WHERE efs.last_status = 'ko'
                            AND efs.ei_function_id = esr.ei_function_id
                            AND ei_iteration_id = :ei_iteration_id)) AS risk_exec_ko
            FROM ei_subject_risk esr
            LEFT OUTER JOIN ei_function_stat eess ON eess.ei_function_id = esr.ei_function_id
            WHERE esr.ei_subject_id = esr.ei_subject_id
            GROUP BY esr.ei_subject_id) esr ON esr.ei_subject_id = s.ei_subject_id
            WHERE s.ei_subject_id=:ei_subject_id
            AND s.ei_subject_version_id=
                (SELECT max(s2.ei_subject_version_id)
                FROM ei_subject s2
                WHERE s2.ei_subject_id=s.ei_subject_id)
            ORDER BY s.ei_subject_id DESC;"
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_iteration_id' => $this->user['current_show_iteration_id']
            ]
        );
        $subject = $s->fetch(PDO::FETCH_ASSOC);

        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT dv.* from ei_doc_version dv where
            dv.ei_doc_version_id=(select max(dv2.ei_doc_version_id) from ei_doc_version dv2 where
            dv.ei_doc_id=dv2.ei_doc_id) and dv.ei_subject_id=:ei_subject_id'
        );

        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );
        $doc = $s->fetchall(PDO::FETCH_ASSOC);

        $campaigns = $this->callClass(
            "Subject", 
            "getSubjectCampaign", 
            [
                'ei_subject_id' => $d->ei_subject_id,
            ]
        );
        // error_log(json_encode($campaigns));
        $subject['campaign'] = $campaigns->getdata();

        $this->setData(
            [
                'doc' => $doc,
                'subject' => $subject
            ]
        );
        
        return true;
    }

    // /**
    //  * Récupération de la liste des campagnes de l'intervention
    //  * 
    //  * @return array
    //  */
    // function getSubjectCampaigns()
    // {
    //     $d = $this->checkParams(
    //         [
    //             'ei_subject_id' => 'int'
    //         ]
    //     );

    //     $s = $this->PDO->prepare(
    //         'SELECT sc.ei_subject_id, sc.ei_subject_campaign_id, sct.name as subject_campaigntype_name, sct.ei_subject_campaigntype_id, sc.label 
    //         from ei_subject_campaign sc left outer join ei_subject_campaigntype sct 
    //         on sc.ei_subject_campaigntype_id=sct.ei_subject_campaigntype_id
    //         where ei_subject_id=:ei_subject_id order by ei_subject_campaign_id desc'
    //     );
    //     $s->execute(
    //         [
    //             'ei_subject_id' => $d->ei_subject_id
    //         ]
    //     );
    //     $subject_campaigns = $s->fetchAll(PDO::FETCH_ASSOC);

    //     $this->setData($subject_campaigns);

    //     return true;
    // }

    /**
     * Récupération de la liste des scénarios présent dans la campagne d'intervention
     * 
     * @return array
     */
    function getSubjectCampaignScenarios()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_subject_campaign_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT scs.ei_subject_id, scs.ei_subject_campaign_id, scs.ei_subject_campaign_scenario_id, s.scenario_name, scs.ei_scenario_id , e.ei_execution_id,e.status, e.expected_date,eu.username
            from ei_subject_campaign_scenario scs 
            left outer join ei_scenario s 
            on scs.ei_scenario_id=s.ei_scenario_id
            left outer join ei_execution e 
            on e.ei_scenario_id=s.ei_scenario_id 
            and e.ei_execution_id=(select max(ei_execution_id) from ei_execution e2 where e2.ei_scenario_id=s.ei_scenario_id)
            left outer join ei_user eu
            on e.ei_user_id=eu.ei_user_id
            where scs.ei_subject_id=:ei_subject_id and scs.ei_subject_campaign_id=:ei_subject_campaign_id'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_subject_campaign_id' => $d->ei_subject_campaign_id
            ]
        );
        $subject_campaign_scenarios = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subject_campaign_scenarios);
        
        return true;
    }

    /**
     * Récupération de la liste des types de campagne d'intervention
     * 
     * @return array
     */
    function getSubjectCampaigntypes()
    {
        $d = $this->checkParams(
            [
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT ei_campaign_type_id,ei_campaign_type_name from ei_campaign_type '
        );
        $s->execute(
            [
            ]
        );
        $subject_campaigntypes = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subject_campaigntypes);

        return true;
    }

    /**
     * Récupération des fonctions liées à l'intervention
     * 
     * @return array
     */
    function getSubjectFunctionEnv()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT fcte.*, t.icon from ei_function_code_techno_environment fcte left outer join ref_techno t on fcte.ref_techno_id=t.ref_techno_id
             where fcte.ei_subject_id=:ei_subject_id group by ei_function_id, ref_techno_id'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );
        $subject_function_env_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subject_function_env_list);

        return true;
    }

    /**
     * Récupération de la liste des interventions 
     * 
     * @return array
     */
    function getSubjectList()
    {
        $s = $this->PDO->prepare(
            "SELECT 
            u3.subject_user_pin,
            esr.risk_exec,
            esr.risk_exec_ok,
            esr.risk_exec_ko,
            esr.total_risk,
            edsre.risk_delivery_exec_ok,
	        edsre.risk_delivery_exec_ko,
            s.ei_subject_id,
            s.ei_subject_external_id,
            s.title,
            p.ei_pool_id,
            p.pool_color,
            p.pool_name,
            d.ei_delivery_id,
            d.delivery_name,
            rds.is_final,
            st.ref_subject_type_id,
            st.type_name,
            st.type_icon,
            ss.ref_subject_status_id,
            ss.status_name,
            ss.color AS status_color,
            ss.status_icon,
            sp.ref_subject_priority_id,
            sp.priority_name,
            sp.color AS priority_color,
            sp.priority_picto,
            u.username,
            u.picture_path,
            u2.username AS in_charge_username,
            u2.picture_path AS in_charge_picture_path,
            s.created_at,
            DATEDIFF(NOW(), s.created_at) AS diff_days
        FROM
            ei_subject s
                LEFT OUTER JOIN
            ei_pool p ON s.ei_pool_id = p.ei_pool_id
                LEFT OUTER JOIN
            ei_delivery d ON s.ei_delivery_id = d.ei_delivery_id
                LEFT OUTER JOIN 
            ref_delivery_status rds ON d.ref_delivery_type_status_id = rds.ref_delivery_type_status_id
                LEFT OUTER JOIN
            ref_subject_type st ON s.ref_subject_type_id = st.ref_subject_type_id
                LEFT OUTER JOIN
            ref_subject_status ss ON s.ref_subject_status_id = ss.ref_subject_status_id
                LEFT OUTER JOIN
            ref_subject_priority sp ON s.ref_subject_priority_id = sp.ref_subject_priority_id
                LEFT OUTER JOIN
            ei_user u ON s.creator_id = u.ei_user_id
                LEFT OUTER JOIN
            ei_user u2 ON s.ei_subject_user_in_charge = u2.ei_user_id
                LEFT OUTER JOIN
            (SELECT 
                 current_subject_id, concat('[',GROUP_CONCAT('{\"username\":\"',username,'\",\"picture_path\":\"',picture_path,'\"}' SEPARATOR ', '),']')
                  AS subject_user_pin
            FROM
                ei_user
            GROUP BY current_subject_id) u3 ON u3.current_subject_id = s.ei_subject_id
                LEFT OUTER JOIN
            (SELECT 
                esr.ei_subject_id,
                    SUM((SELECT 
                            COUNT(1)
                        FROM
                            ei_function_stat efs
                        WHERE
                            efs.nb_ok > 1
                                AND efs.ei_function_id = esr.ei_function_id AND ei_iteration_id=:iteration_id)) AS risk_exec,
                    COUNT(*) AS total_risk,
                    SUM((SELECT 
                            COUNT(1)
                        FROM
                            ei_function_stat efs
                        WHERE
                            efs.last_status = 'ok'
                                AND efs.ei_function_id = esr.ei_function_id
                                AND ei_iteration_id = :iteration_id)) AS risk_exec_ok,
                                SUM((SELECT 
                            COUNT(1)
                        FROM
                            ei_function_stat efs
                        WHERE
                            efs.last_status = 'ko'
                                AND efs.ei_function_id = esr.ei_function_id
                                AND ei_iteration_id = :iteration_id)) AS risk_exec_ko
            FROM
                ei_subject_risk esr
            LEFT OUTER JOIN ei_function_stat eess ON eess.ei_function_id = esr.ei_function_id
            WHERE
                esr.ei_subject_id = esr.ei_subject_id GROUP BY esr.ei_subject_id) esr ON esr.ei_subject_id = s.ei_subject_id
            LEFT OUTER JOIN
	            ei_delivery_subject_risk_exec edsre on edsre.ei_subject_id = s.ei_subject_id
        WHERE
            s.ei_subject_version_id = (SELECT 
                    MAX(s2.ei_subject_version_id)
                FROM
                    ei_subject s2
                WHERE
                    s2.ei_subject_id = s.ei_subject_id) and rds.is_final ='N'
        ORDER BY s.ei_subject_id DESC;"
        );
        $s->execute(['iteration_id' => $this->user['current_show_iteration_id']]);
        $subject_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subject_list);

        return true;
    }

    /**
     * Récupération de la liste des priorités d'intervention
     * 
     * @return array
     */
    function getSubjectPriority()
    {
        $s = $this->PDO->prepare(
            'SELECT * from ref_subject_priority order by `order` asc'
        );
        $s->execute();
        $subject_priority_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subject_priority_list);

        return true;
    }

    /**
     * Récupération de la liste des icones de priorités
     * 
     * @return array
     */
    function getSubjectIconPriority()
    {
        $s = $this->PDO->prepare(
            'SELECT * FROM ref_subject_priority_icon_class;'
        );
        $s->execute();
        $subject_priority_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subject_priority_list);

        return true;
    }

    /**
     * Récupération de la liste des icones de status
     * 
     * @return array
     */
    function getSubjectIconStatus()
    {
        $s = $this->PDO->prepare(
            'SELECT * FROM ref_subject_status_icon_class;'
        );
        $s->execute();
        $subject_status_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subject_status_list);

        return true;
    }

    /**
     * Récupération de la liste des icones des type
     * 
     * @return array
     */
    function getSubjectIconType()
    {
        $s = $this->PDO->prepare(
            'SELECT * FROM ref_subject_type_icon_class;'
        );
        $s->execute();
        $subject_type_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subject_type_list);

        return true;
    }

    /**
     * Récupération de la liste des roles d'intervention
     * 
     * @return array
     */
    function getSubjectRoleList()
    {
        $s = $this->PDO->prepare(
            'SELECT * from ref_subject_role order by `order` asc'
        );
        $s->execute();
        $subject_role_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subject_role_list);

        return true;
    }

    /**
     * Récupération des scénarios liés à l'intervention
     * 
     * @return array
     */
    function getSubjectScenarioEnv()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT * from ei_scenario_version_environment where ei_subject_id=:ei_subject_id group by ei_scenario_id'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );
        $subject_scenario_env_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subject_scenario_env_list);

        return true;
    }

    /**
     * Récupération de la liste des status d'interventions
     * 
     * @return array
     */
    function getSubjectStatusList()
    {
        $s = $this->PDO->prepare(
            'SELECT * from ref_subject_status order by `order` asc'
        );
        $s->execute();
        $subject_status_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subject_status_list);

        return true;
    }

    /**
     * Récupération de la liste des tâches liées à une intervention, pour l'afficher dans le tableau des subject tasks (triés par l'intervention 
     * la plus récente)
     * 
     * @return array
     */
    function getSubjectTaskList() 
    {
        $s = $this->PDO->prepare(
            'SELECT type_prefix,ref_task_type_id,task_type_name,task_type_color,task_type_order,ei_task_id,task_title,overwrite_expected_end,overwrite_expected_start,
            estimation,final_cost,ei_user_id,username,user_picture_path,ref_task_status_id,task_status_name,task_status_color,task_status_icon_class,
            task_status_order,task_status_is_new,task_status_is_inprogress,task_status_is_final,ref_subject_type_id,type_name,ei_subject_id,title,ei_pool_id,
            pool_name,ei_delivery_id,delivery_name,ref_subject_status_id,subject_status_name,subject_status_color,ref_subject_priority_id,
            subject_priority_name,subject_priority_color,created_at
            FROM ei_task_detail_vw 
            WHERE  connected_user_id = :connected_user_id
            ORDER BY ei_subject_id DESC'
        );
        $s->execute(
            ['connected_user_id' => $this->user['ei_user_id']]
        );
        $subject_task_list = $s->fetchAll(PDO::FETCH_ASSOC);

        // $subject_list = [];
        // foreach ($subject_task_list as $index => $task) {
        //     $s = $this->PDO->prepare(
        //         'SELECT s.ei_subject_id, s.title, p.ei_pool_id, p.pool_name, d.ei_delivery_id,
        //         d.delivery_name, st.ref_subject_type_id, st.type_name, 
        //         ss.ref_subject_status_id, ss.status_name, ss.color as status_color,
        //         sp.ref_subject_priority_id, sp.priority_name, sp.color as priority_color,
        //         u.username, u.picture_path, s.created_at, s.description from ei_subject s 
        //         left outer join ei_pool p
        //         on s.ei_pool_id=p.ei_pool_id 
        //         left outer join ei_delivery d
        //         on s.ei_delivery_id=d.ei_delivery_id
        //         left outer join ref_subject_type st
        //         on s.ref_subject_type_id=st.ref_subject_type_id
        //         left outer join ref_subject_status ss
        //         on s.ref_subject_status_id=ss.ref_subject_status_id
        //         left outer join ref_subject_priority sp
        //         on s.ref_subject_priority_id=sp.ref_subject_priority_id
        //         left outer join ei_user u
        //         on s.creator_id=u.ei_user_id 
        //         where s.ei_subject_id=:ei_subject_id and 
        //         s.ei_subject_version_id=(select max(s2.ei_subject_version_id) 
        //         from ei_subject s2 where s2.ei_subject_id=s.ei_subject_id)
        //         order by s.ei_subject_id desc'
        //     );
        //     // error_log($task['ei_subject_id']);
        //     $s->execute(
        //         [
        //             'ei_subject_id' => $task['ei_subject_id']
        //         ]
        //     );
        //     $subject = $s->fetch(PDO::FETCH_ASSOC);

        //     array_push($subject_list, $subject);
        // }

        $this->setData(
            [
                // 'subject_list' => $subject_list, 
                'task_list' => $subject_task_list
            ]
        );

        return true;
    }

    /**
     * Récupération de la liste des taches liées à l'intervention, au type et au statut de la tache
     * 
     * @return array
     */
    function getSubjectTaskListById()
    {
        $d = $this->checkParams(
            [
                'ei_criteria' => 'json',
                'ref_task_type_status_id' => 'int'
            ]
        );
        if ($d->ei_criteria) {
            // error_log($d->ref_task_type_status_id);
            foreach ($d->ei_criteria as $type => $value) {
                foreach ($value as $key2 => $id_value) {
                    // error_log($id_value);
                }
            }
        }
        // error_log($d->ref_task_type_status_id);
        // error_log($type);
        if ($type === 'subject') {
            $s = $this->PDO->prepare(
                "SELECT DISTINCT
                    etdw.type_prefix,
                    etdw.ref_task_type_id,
                    etdw.task_type_name,
                    etdw.task_type_color,
                    etdw.task_type_order,
                    etdw.ei_task_id,
                    etdw.task_title,
                    etdw.overwrite_expected_end,
                    etdw.overwrite_expected_start,
                    etdw.nb_calendardays_before_alert,
                    etdw.alert_LATE,
                    etdw.alert_lastday,
                    etdw.estimation,
                    etdw.final_cost,
                    etdw.ei_user_id,
                    DATE_FORMAT(etdw.overwrite_expected_start, '%d %b') AS expected_start_short,
                    DATE_FORMAT(etdw.overwrite_expected_end, '%d %b') AS expected_end_short,
                    etdw.username,
                    etdw.user_picture_path,
                    etdw.ref_task_status_id,
                    etdw.task_status_name,
                    etdw.task_status_color,
                    etdw.task_status_icon_class,
                    etdw.task_status_order,
                    etdw.task_status_is_new,
                    etdw.task_status_is_inprogress,
                    etdw.task_status_is_final,
                    etdw.task_description,
                    etdw.task_creator_username,
                    etdw.ref_subject_type_id,
                    etdw.type_name,
                    etdw.ei_subject_id,
                    etdw.title,
                    etdw.ei_pool_id,
                    etdw.pool_name,
                    etdw.ei_delivery_id,
                    etdw.delivery_name,
                    etdw.ref_subject_status_id,
                    etdw.subject_status_name,
                    etdw.subject_status_color,
                    etdw.ref_subject_priority_id,
                    etdw.subject_priority_name,
                    etdw.subject_priority_color,
                    etdw.created_at,
                    etdw.isread,
                    etdw.read_dttm,
                    etf.ei_task_id as flag_task_id, 
                    etf.ei_user_id as flag_user_id,
                    etf.ref_flag_id , 
                    rf.icon_class
                FROM
                    ei_task_detail_vw etdw 
                    left outer join ei_task_flag etf  on etf.ei_task_id=etdw.ei_task_id and etf.ei_user_id=:user_id
                    LEFT outer JOIN ref_flag rf ON rf.ref_flag_id=etf.ref_flag_id 
                WHERE
                    ei_subject_id =:ei_subject_id
                        AND ref_task_status_id =:ref_task_type_status_id 
                        AND etdw.connected_user_id = :connected_user_id"
            );
            $s->execute(
                [
                    'ei_subject_id' => $id_value,
                    'ref_task_type_status_id' => $d->ref_task_type_status_id,
                    'user_id' => $this->user['ei_user_id'],
                    'connected_user_id' => $this->user['ei_user_id']
                ]
            );
            $subject_task_list = $s->fetchAll(PDO::FETCH_ASSOC);


           
        } else if ($type === 'delivery') {
            $s = $this->PDO->prepare(
                'SELECT distinct type_prefix,ref_task_type_id,task_type_name,task_type_color,task_type_order,ei_task_id,task_title,overwrite_expected_end,overwrite_expected_start, nb_calendardays_before_alert,alert_LATE ,alert_lastday,
                estimation,final_cost,ei_user_id, DATE_FORMAT(overwrite_expected_start, "%d %b") as expected_start_short, DATE_FORMAT(overwrite_expected_end, "%d %b") as expected_end_short, username,user_picture_path,ref_task_status_id,task_status_name,task_status_color,task_status_icon_class,
                task_status_order,task_status_is_new,task_status_is_inprogress,task_status_is_final,task_description,task_creator_username,ref_subject_type_id,type_name,ei_subject_id,title,ei_pool_id,
                pool_name,ei_delivery_id,delivery_name,ref_subject_status_id,subject_status_name,subject_status_color,ref_subject_priority_id,
                subject_priority_name,subject_priority_color,created_at, isread, read_dttm from ei_task_detail_vw where ei_delivery_id=:ei_delivery_id and ref_task_status_id=:ref_task_type_status_id AND connected_user_id = :connected_user_id '
            );
            $s->execute(
                [
                    'ei_delivery_id' => $id_value,
                    'ref_task_type_status_id' => $d->ref_task_type_status_id,
                    'connected_user_id' => $this->user['ei_user_id']
                ]
            );
            $subject_task_list = $s->fetchAll(PDO::FETCH_ASSOC);

        } else if ($type === 'iteration') {
            $s = $this->PDO->prepare(
                "SELECT distinct tv.* FROM ei_task_detail_vw tv , ei_execution_stack_step_link sl
                where sl.object_id = tv.ei_task_id
                and sl.object_type ='TASK'
                and sl.ei_iteration_id =:ei_iteration_id and ref_task_status_id=:ref_task_type_status_id
                AND tv.connected_user_id = :connected_user_id"
            );
            $s->execute(
                [
                    'ei_iteration_id' => $id_value,
                    'ref_task_type_status_id' => $d->ref_task_type_status_id,
                    'connected_user_id' => $this->user['ei_user_id']
                ]
            );
            $subject_task_list = $s->fetchAll(PDO::FETCH_ASSOC);

        } else if ($type === 'MyFilter') {
            $s = $this->PDO->prepare(
                "SELECT type_prefix,ref_task_type_id,task_type_name,task_type_color,task_type_order,ei_task_id,task_title,overwrite_expected_end,overwrite_expected_start, nb_calendardays_before_alert,alert_LATE ,alert_lastday,
                estimation,final_cost,ei_user_id, DATE_FORMAT(overwrite_expected_start, '%d %b') as expected_start_short, DATE_FORMAT(overwrite_expected_end, '%d %b') as expected_end_short, username,user_picture_path,ref_task_status_id,task_status_name,task_status_color,task_status_icon_class,
                task_status_order,task_status_is_new,task_status_is_inprogress,task_status_is_final,task_description,task_creator_username,ref_subject_type_id,type_name,ei_subject_id,title,ei_pool_id,
                pool_name,ei_delivery_id,delivery_name,ref_subject_status_id,subject_status_name,subject_status_color,ref_subject_priority_id,
                subject_priority_name,subject_priority_color,created_at, isread, read_dttm from ei_task_detail_vw where ( ref_task_status_id in (select ref_task_status_id from ref_task_status where is_inprogress ='Y')  
                OR  ref_subject_status_id in (select ref_subject_status_id from ref_subject_status where is_inprogress ='Y' OR (ref_subject_status_id in (select ref_subject_status_id from ref_subject_status where is_final ='Y' AND   ref_task_status_id not in (select ref_task_status_id from ref_task_status where is_final ='Y')  )
                )) ) and ref_task_status_id=:ref_task_type_status_id AND connected_user_id = :connected_user_id"
            );
            
            $s->execute(
                [
                    'connected_user_id' => $this->user['ei_user_id'],
                    'ref_task_type_status_id' => $d->ref_task_type_status_id,
                ]
            );
            $subject_task_list = $s->fetchAll(PDO::FETCH_ASSOC);

        }
        
        $this->setData($subject_task_list);

        return true;
    }

    /**
     * Récupation des infos du type de tache d'une intervention sélectionné
     * 
     * @return array
     */
    function getSubjectTaskTypeById()
    {
        $d = $this->checkParams(
            [
                'ref_task_type_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT * from ref_task_type where ref_task_type_id=:task_id'
        );
        $s->execute(
            [
                'task_id' => $d->ref_task_type_id
            ]
        );
        $subject_task = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($subject_task);

        return true;
    }

    /**
     * Récupération de la liste des types de taches pour une intervention
     * 
     * @return array
     */
    function getSubjectTaskTypeList()
    {
        $s = $this->PDO->prepare(
            'SELECT * from ref_task_type order by `order` asc'
        );
        $s->execute();
        $subject_task_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subject_task_list);
        
        return true;
    }

    /**
     * Récupération de la liste des status du type de tache choisi
     * 
     * @return array
     */
    function getSubjectTaskStatusList()
    {
        $d = $this->checkParams(
            [
                'ref_task_type_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT tts.ref_task_type_id, ts.ref_task_status_id, ts.task_status_name, ts.color, ts.order, ts.icon_class, ts.is_final, ts.is_new, ts.is_inprogress
            from ref_task_type_status tts inner join ref_task_status ts on ts.ref_task_status_id=tts.ref_task_status_id
            where tts.ref_task_type_id=:ref_task_type_id order by ts.order asc'
        );
        $s->execute(
            [
                'ref_task_type_id' => $d->ref_task_type_id
            ]
        );
        $subject_task_status_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subject_task_status_list);

        return true;
    }

    /**
     * Récupération de la liste des types d'interventions
     * 
     * @return array
     */
    function getSubjectTypeList()
    {
        $s = $this->PDO->prepare(
            'SELECT * from ref_subject_type order by `order` asc'
        );
        $s->execute();
        $subject_type_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subject_type_list);

        return true;
    }

    /**
     * Récupération de la liste des status de taches disponibles
     * 
     * @return array
     */
    function getTaskStatusList()
    {
        $s = $this->PDO->prepare(
            'SELECT * from ref_task_status order by `order` asc'
        );
        $s->execute();
        $task_status_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($task_status_list);

        return true;
    }

    /**
     * Récupération de la liste des status des execrequest
     * 
     * @return array
     */
    function getExecrequestStatusList()
    {
        $s = $this->PDO->prepare(
            'SELECT * from ref_execrequest_status order by `order` asc'
        );
        $s->execute();
        $execrequest_status_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($execrequest_status_list);

        return true;
    }

    /**
     * Récupération de la liste des types de taches disponibles
     * 
     * @return array
     */
    function getTaskTypeList()
    {
        $s = $this->PDO->prepare(
            'SELECT * from ref_task_type'
        );
        $s->execute();
        $task_type_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($task_type_list);

        return true;
    }

    /**
     * Récupération des ifnormations d'un type de taches
     * 
     * @return array
     */
    function getTaskTypeDetails()
    {

        $d = $this->checkParams(
            [
                'ref_task_type_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT * from ref_task_type where ref_task_type_id=:ref_task_type_id'
        );
        $s->execute(
            [
                'ref_task_type_id' => $d->ref_task_type_id
            ]
        );
        $task_type_detail = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($task_type_detail);

        return true;
    }

    /**
     * Récupération de la liste des status par rapport au type de tache sélectionné
     * 
     * @return array
     */
    function getTaskTypeStatusListByTaskType()
    {
        $d = $this->checkParams(
            [
                'ref_task_type_id' => 'int'
            ]
        );
        // error_log($d->ref_task_type_id);
        $s = $this->PDO->prepare(
            'SELECT rtt.ref_task_type_id, rts.ref_task_status_id, rts.task_status_name, rts.color , rts.icon_class
            from ref_task_type_status rtts 
            inner join ref_task_type rtt on rtt.ref_task_type_id=rtts.ref_task_type_id 
            inner join ref_task_status rts on rts.ref_task_status_id=rtts.ref_task_status_id
            where rtts.ref_task_type_id=:ref_task_type_id order by rts.order'
        );
        $s->execute(
            [
                'ref_task_type_id' => $d->ref_task_type_id
            ]
        );
        $task_type_status_list = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($task_type_status_list);

        return true;
    }

    /**
     * Récupération de la liste des utilisateurs
     * 
     * @return array
     */
    function getUserList() 
    {
        $s = $this->PDO->prepare(
            'SELECT ei_user_id, username , picture_path from ei_user order by ei_user_id asc'
        );
        $s->execute();
        $user_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($user_list);

        return true;
    }

    /**
     * Récupération de la liste des user qui ont le rôle sélectionné
     * 
     * @return array
     */
    function getUserListByRole()
    {
        $d = $this->checkParams(
            [
                'ei_api_role_id' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT u.username from ei_api_role_user ru inner join 
            ei_user u on ru.ei_user_id=u.ei_user_id where ei_api_role_id=
            :ei_api_role_id'
        );
        $s->execute(
            [
                'ei_api_role_id' => $d->ei_api_role_id
            ]
        );
        $user_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($user_list);

        return true;
    }

    /**
     * Récupération de la liste des statuts pour le type de tache du type d'intervention de la pool
     * 
     * @return true
     */
    function getPoolSubjectTypeTaskTypeStatusList()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int',
                'ref_task_type_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT tts.ei_pool_subject_type_task_type_status_id, tts.ei_pool_id, tts.ref_subject_type_id, tts.ref_task_type_id, tts.ref_task_status_id, 
            ts.task_status_name, ts.color, tts.order from ei_pool_subject_type_task_type_status tts 
            left outer join ref_task_status ts
            on tts.ref_task_status_id=ts.ref_task_status_id
            where tts.ei_pool_id=:ei_pool_id
            and tts.ref_subject_type_id=:ref_subject_type_id
            and tts.ref_task_type_id=:ref_task_type_id order by tts.order asc'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ref_task_type_id' => $d->ref_task_type_id
            ]
        );
        $task_status_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($task_status_list);

        return true;
    }

    /**
     * Migration des fonctions sur l'intervention sélectionnée et la techno par défaut
     * 
     * @return true
     */
    function migrateSubjectFunction()
    {
        $d = $this->checkParams(
            [
                'function_list' => 'array',
                'ei_environment_id' => 'int',
                'ei_subject_id' => 'int'
            ]
        );

        foreach ($d->function_list as $index => $ei_function) {
            // On vérifie si la techno et l'intervention sont actifs ou non sur la fonction, si oui on ne fait rien
            $s = $this->PDO->prepare(
                'SELECT * from ei_function_code_techno_environment where ei_function_id=:ei_function_id and ei_environment_id=:ei_environment_id 
                order by effective_date desc limit 1;'
            );
            $s->execute(
                [
                    'ei_function_id' => $ei_function->ei_function_id,
                    'ei_environment_id' => $d->ei_environment_id
                ]
            );
            $current_activated = $s->fetch(PDO::FETCH_ASSOC);

            if ($current_activated['ei_environment_id'] == $d->ei_environment_id && $current_activated['ei_subject_id'] == $d->ei_subject_id
                && $current_activated['ref_techno_id'] == $ei_function->ref_techno_id
            ) {
                // L'environnement et l'intervention sont déjà actifs sur la fonction, on ne fait pas de migration
            } else {
                $this->callClass(
                    "Functions", 
                    "migrateEnvironment", 
                    [
                        'ei_environment_id' => $d->ei_environment_id,
                        'ei_function_id' => $ei_function->ei_function_id,
                        'ref_techno_id' => $ei_function->ref_techno_id,
                        'ei_subject_id' => $d->ei_subject_id
                    ]
                );
            }
        }

        return true;
    }

    /**
     * Migration des scénarios liés à l'intervention
     * 
     * @return true
     */
    function migrateSubjectScenarios()
    {
        $d = $this->checkParams(
            [
                'scenario_list' => 'array',
                'ei_environment_id' => 'int',
                'ei_subject_id' => 'int'
            ]
        );

        foreach ($d->scenario_list as $index => $scenario_id) {
            // On vérifie si l'env est actif sur le scénario avec cette intervention
            $s = $this->PDO->prepare(
                'SELECT sve.* from ei_scenario_version_environment sve where sve.ei_scenario_id=:ei_scenario_id and sve.ei_environment_id=:ei_environment_id 
                and sve.effective_date=(select max(effective_date) from ei_scenario_version_environment sve2 where 
                sve2.ei_environment_id=sve.ei_environment_id and sve2.ei_scenario_id=sve.ei_scenario_id);'
            );
            $s->execute(
                [
                    'ei_scenario_id' => $scenario_id,
                    'ei_environment_id' => $d->ei_environment_id
                ]
            );
            $current_activated = $s->fetch(PDO::FETCH_ASSOC);

            if ($current_activated['ei_environment_id'] == $d->ei_environment_id && $current_activated['ei_subject_id'] == $d->ei_subject_id) {
                // On ne fait rien, l'environnement est déjà actif
            } else {
                $this->callClass(
                    "Scenario", 
                    "migrateEnvironment",
                    [
                        'ei_scenario_id' => $scenario_id,
                        'ei_environment_id' => $d->ei_environment_id,
                        'ei_subject_id' => $d->ei_subject_id
                    ]
                );
            }
        }
    }

    /**
     * Modification d'une livraison
     * 
     * @return true
     */
    function updateDelivery()
    {
        $d = $this->checkParams(
            [
                'ei_delivery_id' => 'int',
                'delivery_name' => 'string',
                'delivery_date' => 'string',
                'ref_delivery_type_status_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'UPDATE ei_delivery set delivery_name=:delivery_name, 
            delivery_date=:delivery_date,ref_delivery_type_status_id=:ref_delivery_type_status_id where ei_delivery_id=:ei_delivery_id'
        );
        $s->execute(
            [
                'delivery_name' => $d->delivery_name,
                'delivery_date' => $d->delivery_date,
                'ei_delivery_id' => $d->ei_delivery_id,
                'ref_delivery_type_status_id' => $d->ref_delivery_type_status_id
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT d.ei_delivery_id, d.delivery_name, d.delivery_date, d.ref_delivery_type_status_id, rds.ref_delivery_type_id, 
            rds.ref_delivery_type_status_id, rds.delivery_status_name, rds.color, rds.delivery_order FROM ei_delivery d, ref_delivery_status rds 
            where d.ref_delivery_type_status_id=rds.ref_delivery_type_status_id and d.ei_delivery_id=:ei_delivery_id'
        );
        $s->execute(
            [
                'ei_delivery_id' => $d->ei_delivery_id
            ]
        );
        $delivery = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($delivery);

        return true;
    }
    
    /**
     * Modificartion du nom de la pool
     * 
     * @return true
     */
    function updatePool()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'pool_color' => 'string',
                'pool_name' => 'string',
                'order' => 'int'
            ]
        );
        // error_log($d->pool_color);
        // On vérifie que le nom de la pool n'existe pas déjà
        $s = $this->PDO->prepare(
            'SELECT pool_name from ei_pool where ei_pool_id!=:ei_pool_id and 
            pool_name=:pool_name'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'pool_name' => $d->pool_name
            ]
        );
        $pool_exists = $s->fetch();

        if ($pool_exists != false) {
            $this->logError(
                'Pool name already exists', 0
            );
        } else {
            $s = $this->PDO->prepare(
                'UPDATE ei_pool set pool_name=:pool_name, `order`=:order, pool_color=:pool_color where 
                ei_pool_id=:ei_pool_id'
            );
            $s->execute(
                [
                    'pool_name' => $d->pool_name,
                    'pool_color' => $d->pool_color,
                    'order' => $d->order,
                    'ei_pool_id' => $d->ei_pool_id
                ]
            );
        }

        $s = $this->PDO->prepare(
            'SELECT * from ei_pool where ei_pool_id=:ei_pool_id order by `order` asc'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id
            ]
        );
        $pool = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($pool);

        return true;
    }

    /**
     * Modification des colonnes par défaut de la pool
     * 
     * @return true
     */
    function updatePoolDefaultValues()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'default_subject_type' => 'int',
                'default_subject_status' => 'int',
                'default_subject_priority' => 'int',
                'default_delivery' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'UPDATE ei_pool set default_subject_type=:default_subject_type,
            default_subject_status=:default_subject_status, 
            default_subject_priority=:default_subject_priority,
            default_delivery=:default_delivery where ei_pool_id=:ei_pool_id'
        );
        $s->execute(
            [
                'default_subject_type' => $d->default_subject_type,
                'default_subject_status' => $d->default_subject_status,
                'default_subject_priority' => $d->default_subject_priority,
                'default_delivery' => $d->default_delivery,
                'ei_pool_id' => $d->ei_pool_id
            ]
        );

        return true;
    }

    
    /**
     * Modification des infso du projet
     * 
     * @return true
     */
    function updateProjectInfos()
    {
        $d = $this->checkParams(
            [
                'project_icon_url' => 'string',
                'project_text' => 'string',
                'project_bg_color' => 'string',
                'project_external_url' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            'UPDATE `ref_install` SET `Project_icon_url`=:project_icon_url, `project_text`=:project_text, `project_bg_color`=:project_bg_color, `external_url`=:project_external_url'
        );
        $s->execute(
            [
                'project_icon_url' => $d->project_icon_url,
                'project_text' => $d->project_text,
                'project_bg_color' => $d->project_bg_color,
                'project_external_url' => $d->project_external_url
            ]
        );

        return true;
    }

    /**
     * Modification de l'ordre de la priorité de l'intervention dans la pool
     * 
     * @return true
     */
    function updatePoolSubjectPriority()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_priority_id' => 'int',
                'order' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'UPDATE ei_pool_subject_priority set `order`=:order where 
            ei_pool_id=:ei_pool_id and ref_subject_priority_id=
            :ref_subject_priority_id'
        );
        $s->execute(
            [
                'order' => $d->order,
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_priority_id' => $d->ref_subject_priority_id
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT sp.ref_subject_priority_id, sp.priority_name, sp.color, 
            psp.order from ei_pool_subject_priority psp, ref_subject_priority sp where 
            psp.ref_subject_priority_id=:ref_subject_priority_id and 
            psp.ei_pool_id=:ei_pool_id'
        );
        $s->execute(
            [
                'ref_subject_priority_id' => $d->ref_subject_priority_id,
                'ei_pool_id' => $d->ei_pool_id
            ]
        );
        $priority = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($priority);

        return true;
    }

    /**
     * Modification du nom du status (override) et du is final par rapport à 
     * un type d'intervention et un pool
     * 
     * @return true
     */
    function updatePoolSubjectStatus()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int',
                'ref_subject_status_id' => 'int',
                'status_name' => 'string',
                'is_final' => 'string',
                'order' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'UPDATE ei_pool_subject_status set status_name=:status_name, 
            is_final=:is_final, `order`=:order where ei_pool_id=:ei_pool_id and 
            ref_subject_type_id=:ref_subject_type_id and 
            ref_subject_status_id=:ref_subject_status_id'
        );
        $s->execute(
            [
                'status_name' => $d->status_name,
                'is_final' => $d->is_final,
                'order' => $d->order,
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ref_subject_status_id' => $d->ref_subject_status_id
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT ss.ref_subject_status_id, ss.color, ss.status_name,
            pss.status_name as status_name_override, 
            pss.is_final, pss.order from ei_pool_subject_status pss, 
            ref_subject_status ss where pss.ref_subject_status_id=ss.ref_subject_status_id
            and pss.ei_pool_id=:ei_pool_id and pss.ref_subject_type_id=
            :ref_subject_type_id and ss.ref_subject_status_id=:ref_subject_status_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ref_subject_status_id' => $d->ref_subject_status_id
            ]
        );
        $status = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($status);

        return true;
    }

    /**
     * Modification de l'ordre d'un type d'intervention de la pool
     * 
     * @return true
     */
    function updatePoolSubjectType()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int',
                'order' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'UPDATE ei_pool_subject_type set `order`=:order where ei_pool_id=
            :ei_pool_id and ref_subject_type_id=:ref_subject_type_id'
        );
        $s->execute(
            [
                'order' => $d->order,
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT st.ref_subject_type_id, st.type_name, pst.order 
            from ei_pool_subject_type pst, ref_subject_type st where 
            pst.ref_subject_type_id=:ref_subject_type_id and 
            pst.ei_pool_id=:ei_pool_id'
        );
        $s->execute(
            [
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ei_pool_id' => $d->ei_pool_id
            ]
        );
        $type = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($type);
        
        return true;
    }

    /**
     * Update des status task
     * 
     * @return true;
     */
    function updatePoolSubjectTypeTaskTypeTaskStatus()
    {
        $d = $this->checkParams(
            [
                'ei_pool_subject_type_task_type_status_id' => 'int',
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int',
                'ref_task_type_id' => 'int',
                'ref_task_status_id' => 'int',
                'order' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT * from ei_pool_subject_type_task_type_status where ei_pool_id=:ei_pool_id and ref_subject_type_id=:ref_subject_type_id and 
            ref_task_type_id=:ref_task_type_id and ref_task_status_id=:ref_task_status_id and 
            ei_pool_subject_type_task_type_status_id!=:ei_pool_subject_type_task_type_status_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ref_task_type_id' => $d->ref_task_type_id,
                'ref_task_status_id' => $d->ref_task_status_id,
                'ei_pool_subject_type_task_type_status_id' => $d->ei_pool_subject_type_task_type_status_id
            ]
        );
        $task_status_exists = $s->fetch();

        if ($task_status_exists != false) {
            $this->logError(
                'Task status already exists in pool subject type task type', 0
            );
        } else {
            $s = $this->PDO->prepare(
                'UPDATE ei_pool_subject_type_task_type_status set ref_task_status_id=:ref_task_status_id, `order`=:order where ei_pool_id=:ei_pool_id and
                ref_subject_type_id=:ref_subject_type_id and ref_task_type_id=:ref_task_type_id and ei_pool_subject_type_task_type_status_id
                =:ei_pool_subject_type_task_type_status_id'
            );
            $s->execute(
                [
                    'ref_task_status_id' => $d->ref_task_status_id,
                    'order' => $d->order,
                    'ei_pool_id' => $d->ei_pool_id,
                    'ref_subject_type_id' => $d->ref_subject_type_id,
                    'ref_task_type_id' => $d->ref_task_type_id,
                    'ei_pool_subject_type_task_type_status_id' => $d->ei_pool_subject_type_task_type_status_id
                ]
            );
        }

        $s = $this->PDO->prepare(
            'SELECT tts.ei_pool_subject_type_task_type_status_id, tts.ei_pool_id, tts.ref_subject_type_id, tts.ref_task_type_id, tts.ref_task_status_id, 
            ts.task_status_name, ts.color, tts.order from ei_pool_subject_type_task_type_status tts 
            left outer join ref_task_status ts
            on tts.ref_task_status_id=ts.ref_task_status_id
            where tts.ei_pool_id=:ei_pool_id
            and tts.ref_subject_type_id=:ref_subject_type_id
            and tts.ref_task_type_id=:ref_task_type_id and ei_pool_subject_type_task_type_status_id=:ei_pool_subject_type_task_type_status_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ref_task_type_id' => $d->ref_task_type_id,
                'ei_pool_subject_type_task_type_status_id' => $d->ei_pool_subject_type_task_type_status_id
            ]
        );
        $status = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($status);
        
        return true;
    }

    /**
     * Modification du type de campagne du type d'intervention de la pool
     * 
     * @return true
     */
    function updatePoolSubjectTypeCampaignType()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int',
                'ei_subject_campaigntype_id' => 'int',
                'name' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            'UPDATE ei_subject_campaigntype set name=:name where ei_pool_id=:ei_pool_id and 
            ref_subject_type_id=:ref_subject_type_id and ei_subject_campaigntype_id=:ei_subject_campaigntype_id'
        );
        $s->execute(
            [
                'name' => $d->name,
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ei_subject_campaigntype_id' => $d->ei_subject_campaigntype_id
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT * from ei_subject_campaigntype where ei_pool_id=:ei_pool_id and ref_subject_type_id=:ref_subject_type_id and 
            ei_subject_campaigntype_id=:ei_subject_campaigntype_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ei_subject_campaigntype_id' => $d->ei_subject_campaigntype_id
            ]
        );
        $campaign = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($campaign);

        return true;
    }

    /**
     * Modification du type de la tache du type d'intervention de la pool
     * 
     * @return true
     */
    function updatePoolSubjectTypeTaskType()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int',
                'ref_subject_type_id' => 'int',
                'ref_task_type_id' => 'int',
                'created_by_default' => 'string',
                'default_title' => 'string',
                'default_estimation' => 'int',
                'default_user_id' => 'int',
                'default_task_status_id' => 'int',
                'order' => 'int'
            ]
        );

        // Si on a choisi created_by_default a Yes, on vérifie s'il y a un statut
        if ($d->created_by_default == 'Y' && $d->default_task_status_id == 0) {
            // On retourne une erreur
            $this->logError(
                'Task created by default needs a status !', 0
            );
        } else {
            // Tout va bien
            $s = $this->PDO->prepare(
                'UPDATE ei_pool_subject_type_task_type set created_by_default=
                :created_by_default, default_title=:default_title,
                default_estimation=:default_estimation, default_user_id=:default_user_id,
                default_task_status_id=:default_task_status_id, `order`=:order where 
                ei_pool_id=:ei_pool_id and ref_subject_type_id=:ref_subject_type_id and
                ref_task_type_id=:ref_task_type_id'
            );
            $s->execute(
                [
                    'created_by_default' => $d->created_by_default,
                    'default_title' => $d->default_title,
                    'default_estimation' => $d->default_estimation,
                    'default_user_id' => $d->default_user_id,
                    'default_task_status_id' => $d->default_task_status_id,
                    'order' => $d->order,
                    'ei_pool_id' => $d->ei_pool_id,
                    'ref_subject_type_id' => $d->ref_subject_type_id,
                    'ref_task_type_id' => $d->ref_task_type_id
                ]
            );
        }

        $s = $this->PDO->prepare(
            'SELECT psttt.ei_pool_id, psttt.ref_subject_type_id, psttt.ref_task_type_id, 
            rtt.task_type_name, rtt.color, psttt.created_by_default, 
            psttt.default_estimation, psttt.default_user_id, u.username, 
            psttt.default_task_status_id, rts.task_status_name, 
            rts.color as status_color, psttt.order, psttt.default_title
            from ei_pool_subject_type_task_type psttt 
            left outer join ref_task_type rtt
            on psttt.ref_task_type_id=rtt.ref_task_type_id
            left outer join ei_user u
            on psttt.default_user_id=u.ei_user_id
            left outer join ei_pool_subject_type_task_type_status psttts
            on psttt.ref_task_type_id=psttts.ref_task_type_id and
            psttt.default_task_status_id=psttts.ref_task_status_id and psttt.ei_pool_id=psttts.ei_pool_id and
            psttt.ref_subject_type_id=psttts.ref_subject_type_id
            left outer join ref_task_status rts on psttts.ref_task_status_id=rts.ref_task_status_id
            where psttt.ei_pool_id=:ei_pool_id and psttt.ref_subject_type_id=
            :ref_subject_type_id and rtt.ref_task_type_id=:ref_task_type_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ref_task_type_id' => $d->ref_task_type_id
            ]
        );
        $type = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($type);

        return true;
    }

    /**
     * Modification d'une intervention (création d'une nouvelle version)
     * 
     * @return true
     */
    function updateSubject()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_pool_id' => 'int',
                'ei_delivery_id' => 'int',
                'ref_subject_type_id' => 'int',
                'ref_subject_status_id' => 'int',
                'ref_subject_priority_id' => 'int',
                'ei_subject_user_in_charge' => 'int',
                'title' => 'html',
                'description' => 'html',
                'patch_title' => 'html',
                'patch_description' => 'html',
                'patch_type_id' => 'int',
                'patch_status_id' => 'int',
            ]
        );

        $d = $this->initOptionalParams('ei_subject_external_id', 'string', '');
        if ($d->description !== 0) {
            $description = urldecode($d->description);
            // error_log($description);
        } else {
            // Si la description est nulle, on a édité l'intervention depuis la liste d'intervention on récupère donc la description précédente
            $s = $this->PDO->prepare(
                'SELECT description from ei_subject where ei_subject_id=:ei_subject_id and ei_subject_version_id=(select max(ei_subject_version_id) 
                from ei_subject where ei_subject_id=:ei_subject_id)'
            );
            $s->execute(
                [
                    'ei_subject_id' => $d->ei_subject_id
                ]
            );
            $description = $s->fetch()[0];
        }

        $title = urldecode($d->title);

        // Récupération de la version maximale de la version (on est au moins à la 1)
        $s = $this->PDO->prepare(
            'SELECT max(ei_subject_version_id)+1 from ei_subject where 
            ei_subject_id=:ei_subject_id'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );
        $max_version_id = (int)($s->fetch()?:[0])[0]; 

        // Récupération de la pool d'origine de l'intervention (version id)
        $s = $this->PDO->prepare(
            'SELECT ei_pool_origin, creator_id, created_at from ei_subject 
            where ei_subject_id=:ei_subject_id and ei_subject_version_id=1'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );
        $old_values = $s->fetch(PDO::FETCH_ASSOC);


        $s = $this->PDO->prepare(
            'SELECT s.ei_subject_id, s.title, p.ei_pool_id, p.pool_name, d.ei_delivery_id,
            d.delivery_name, st.ref_subject_type_id, st.type_name, 
            ss.ref_subject_status_id, ss.status_name, ss.color as status_color,
            sp.ref_subject_priority_id, sp.priority_name, sp.color as priority_color,
            u.username, u.picture_path, s.created_at, s.description,  esp.title as patch_title, esp.description as patch_description ,esp.ref_patch_status_id as patch_status, esp.ref_patch_type_id as patch_type from ei_subject s 
            left outer join ei_pool p
            on s.ei_pool_id=p.ei_pool_id 
            left outer join ei_delivery d
            on s.ei_delivery_id=d.ei_delivery_id
            left outer join ref_subject_type st
            on s.ref_subject_type_id=st.ref_subject_type_id
            left outer join ref_subject_status ss
            on s.ref_subject_status_id=ss.ref_subject_status_id
            left outer join ref_subject_priority sp
            on s.ref_subject_priority_id=sp.ref_subject_priority_id
            left outer join ei_subject_patch esp
            on esp.ei_subject_id=s.ei_subject_id and (select max(ei_subject_patch_version_id)FROM ei_subject_patch where ei_subject_id=s.ei_subject_id)=esp.ei_subject_patch_version_id
            left outer join ei_user u
            on s.creator_id=u.ei_user_id 
            where s.ei_subject_id=:ei_subject_id and 
            s.ei_subject_version_id=(select max(s2.ei_subject_version_id) 
            from ei_subject s2 where s2.ei_subject_id=s.ei_subject_id)
            order by s.ei_subject_id desc'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );
        $old_values_subject = $s->fetch(PDO::FETCH_ASSOC);

        /* On insère la nouvelle version de l'intervention : la pool d'origine ne 
        change pas, par contre le created at je sais pas ???
        */
        $s = $this->PDO->prepare(
            'INSERT into ei_subject(ei_subject_id,ei_subject_external_id, ei_subject_version_id, ei_pool_id,
            ei_pool_origin, ei_delivery_id, ref_subject_type_id, ref_subject_status_id,
            ref_subject_priority_id, title, description,ei_subject_user_in_charge, creator_id, created_at) 
            values(:ei_subject_id,:ei_subject_external_id, :ei_subject_version_id, :ei_pool_id, 
            :ei_pool_origin, :ei_delivery_id, :ref_subject_type_id, :ref_subject_status_id,
            :ref_subject_priority_id, :title, :description,:ei_subject_user_in_charge, :creator_id,
            :created_at)'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_subject_external_id' => $d->ei_subject_external_id,
                'ei_subject_version_id' => $max_version_id,
                'ei_pool_id' => $d->ei_pool_id,
                'ei_pool_origin' => $old_values['ei_pool_origin'],
                'ei_delivery_id' => $d->ei_delivery_id,
                'ref_subject_type_id' => $d->ref_subject_type_id,
                'ref_subject_status_id' => $d->ref_subject_status_id,
                'ref_subject_priority_id' => $d->ref_subject_priority_id,
                'title' => $title,
                'description' => str_replace('<img', '<img class="img-fluid"', $description),
                'ei_subject_user_in_charge' => $d->ei_subject_user_in_charge,
                'creator_id' => $old_values['creator_id'],
                'created_at' => $old_values['created_at']
            ]
        );
        if ($old_values_subject && ($old_values_subject['patch_title'] != $d->patch_title || $old_values_subject['patch_description'] != $d->patch_description || $old_values_subject['patch_type'] != $d->patch_type_id || $old_values_subject['patch_status'] != $d->patch_status_id)) {
            
            $s = $this->PDO->prepare(
                'SELECT max(ei_subject_patch_version_id)+1 from ei_subject_patch where 
                ei_subject_id=:ei_subject_id'
            );
            $s->execute(
                [
                    'ei_subject_id' => $d->ei_subject_id
                ]
            );
            $max_version_id = (int)($s->fetch()?:[0])[0]; 
            if ($max_version_id == 0 ) {
                $max_version_id =1;
            }


            // error_log('title ou description changed');
            $s = $this->PDO->prepare(
                'INSERT INTO `ei_subject_patch` 
                (`ei_subject_id`, `ei_subject_patch_version_id`, `title`, `description`, `ref_patch_status_id`, `ref_patch_type_id`, `created_by`, `created_at`) 
                VALUES (:ei_subject_id, :patch_version_max, :patch_title, :patch_description,:patch_status_id, :patch_type_id, :user_id, now())'
            );
            $s->execute(
                [
                    'ei_subject_id' => $d->ei_subject_id,
                    'patch_version_max' => $max_version_id,
                    'patch_title' => $d->patch_title,
                    'patch_status_id' => $d->patch_status_id,
                    'patch_type_id' => $d->patch_type_id,
                    'patch_description' =>$d->patch_description,
                    'user_id' => $this->user['ei_user_id'],
                ]
            );
        }

        $s = $this->PDO->prepare(
            'SELECT s.ei_subject_id, s.title, p.ei_pool_id, p.pool_name, d.ei_delivery_id,
            d.delivery_name, st.ref_subject_type_id, st.type_name, 
            ss.ref_subject_status_id, ss.status_name, ss.color as status_color,
            sp.ref_subject_priority_id, sp.priority_name, sp.color as priority_color,
            u.username, u.picture_path, s.created_at, s.description from ei_subject s 
            left outer join ei_pool p
            on s.ei_pool_id=p.ei_pool_id 
            left outer join ei_delivery d
            on s.ei_delivery_id=d.ei_delivery_id
            left outer join ref_subject_type st
            on s.ref_subject_type_id=st.ref_subject_type_id
            left outer join ref_subject_status ss
            on s.ref_subject_status_id=ss.ref_subject_status_id
            left outer join ref_subject_priority sp
            on s.ref_subject_priority_id=sp.ref_subject_priority_id
            left outer join ei_user u
            on s.creator_id=u.ei_user_id 
            where s.ei_subject_id=:ei_subject_id and 
            s.ei_subject_version_id=(select max(s2.ei_subject_version_id) 
            from ei_subject s2 where s2.ei_subject_id=s.ei_subject_id)
            order by s.ei_subject_id desc'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );
        $new_values_subject = $s->fetch(PDO::FETCH_ASSOC);

        if ($d->ref_subject_type_id != $old_values_subject['ref_subject_type_id']) {
            $this->callClass(
                "Subject", 
                "addSubjectAudit", 
                [
                    'ei_subject_id' => $d->ei_subject_id,
                    'ei_description' => "updated subject type from <strong>".$old_values_subject['type_name'].'</strong> to <strong>'.$new_values_subject['type_name'].'</strong>',
                    'element_type' => "SUBJECT",
                    'element_id' => $d->ei_subject_id,
                    'label' => '',
                    'action' => "UPDATE"
                ]
            );
        }

        if ($d->ref_subject_status_id != $old_values_subject['ref_subject_status_id']) {
            $this->callClass(
                "Subject", 
                "addSubjectAudit", 
                [
                    'ei_subject_id' => $d->ei_subject_id,
                    'ei_description' => "updated subject status from <strong>".$old_values_subject['status_name'].'</strong> to <strong>'.$new_values_subject['status_name'].'</strong>',
                    'element_type' => "SUBJECT",
                    'element_id' => $d->ei_subject_id,
                    'label' => '',
                    'action' => "UPDATE"
                ]
            );
        }

        if ($d->ref_subject_priority_id != $old_values_subject['ref_subject_priority_id']) {
            $this->callClass(
                "Subject", 
                "addSubjectAudit", 
                [
                    'ei_subject_id' => $d->ei_subject_id,
                    'ei_description' => "updated subject priority from <strong>".$old_values_subject['priority_name'].'</strong> to <strong>'.$new_values_subject['priority_name'].'</strong>',
                    'element_type' => "SUBJECT",
                    'element_id' => $d->ei_subject_id,
                    'label' => '',
                    'action' => "UPDATE"
                ]
            );
        }
        
        if ($description != $old_values_subject['description']) {
            $this->callClass(
                "Subject", 
                "addSubjectAudit", 
                [
                    'ei_subject_id' => $d->ei_subject_id,
                    'ei_description' => 'updated subject description',
                    'element_type' => "SUBJECTDESC",
                    'element_id' => $d->ei_subject_id,
                    'label' => '',
                    'action' => "UPDATE"

                ]
            );
        }

        if ($title != $old_values_subject['title']) {
            $this->callClass(
                "Subject", 
                "addSubjectAudit", 
                [
                    'ei_subject_id' => $d->ei_subject_id,
                    'ei_description' => 'updated subject tile from <strong> '.$old_values_subject['title'].'</strong> to <strong>'.$title.'</strong>',
                    'element_type' => "SUBJECT",
                    'element_id' => $d->ei_subject_id,
                    'label' => '',
                    'action' => "UPDATE"

                ]
            );
        }

        $s = $this->PDO->prepare(
            'SELECT s.ei_subject_id, s.title, p.ei_pool_id, p.pool_name, d.ei_delivery_id,
            d.delivery_name, st.ref_subject_type_id, st.type_name, 
            ss.ref_subject_status_id, ss.status_name, ss.color as status_color,
            sp.ref_subject_priority_id, sp.priority_name, sp.color as priority_color,
            u.username, u.picture_path, s.created_at from ei_subject s 
            left outer join ei_pool p
            on s.ei_pool_id=p.ei_pool_id 
            left outer join ei_delivery d
            on s.ei_delivery_id=d.ei_delivery_id
            left outer join ref_subject_type st
            on s.ref_subject_type_id=st.ref_subject_type_id
            left outer join ref_subject_status ss
            on s.ref_subject_status_id=ss.ref_subject_status_id
            left outer join ref_subject_priority sp
            on s.ref_subject_priority_id=sp.ref_subject_priority_id
            left outer join ei_user u
            on s.creator_id=u.ei_user_id 
            where s.ei_subject_id=:ei_subject_id and s.ei_subject_version_id=(select max(s2.ei_subject_version_id) 
            from ei_subject s2 where s2.ei_subject_id=:ei_subject_id)'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );
        $subject = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($subject);

        return true;
    }

    /**
     * Modification du nom et de la couleur de la priorité de l'intervention
     * 
     * @return true
     */
    function updateSubjectPriority()
    {
        $d = $this->checkParams(
            [
                'ref_subject_priority_id' => 'int',
                'priority_name' => 'string',
                'color' => 'string',
                'order' => 'int',
                'SubjectPriorityIcon' => 'string'
            ]
        );

        // Si le nom, la couleur et l'ordre n'ont pas changé on ne fait rien
        $s = $this->PDO->prepare(
            'SELECT priority_name, color, `order`,priority_picto from ref_subject_priority where 
            ref_subject_priority_id=:ref_subject_priority_id'
        );
        $s->execute(
            [
                'ref_subject_priority_id' => $d->ref_subject_priority_id
            ]
        );
        $old_priority = $s->fetch(PDO::FETCH_ASSOC);

        if ($d->priority_name == $old_priority['priority_name']
            && $d->color == $old_priority['color']
            && $d->order == $old_priority['order']
             && $d->SubjectPriorityIcon == $old_priority['priority_picto']
        ) {
            // le nom, la couleur et l'ordre n'ont pas changé
        } else {
            $s = $this->PDO->prepare(
                'SELECT * from ref_subject_priority where priority_name=:priority_name
                and ref_subject_priority_id!=:ref_subject_priority_id'
            );
            $s->execute(
                [
                    'priority_name' => $d->priority_name,
                    'ref_subject_priority_id' => $d->ref_subject_priority_id
                ]
            );
            $subject_priority_exists = $s->fetch();

            if ($subject_priority_exists != false) {
                $this->logError(
                    'Subject priority name already exists', 0
                );
            } else {
                $s = $this->PDO->prepare(
                    'UPDATE ref_subject_priority set priority_name=:priority_name,
                    color=:color, `order`=:order, `priority_picto`=:SubjectPriorityIcon where 
                    ref_subject_priority_id=:ref_subject_priority_id'
                );
                $s->execute(
                    [
                        'priority_name' => $d->priority_name,
                        'color' => $d->color,
                        'order' => $d->order,
                        'ref_subject_priority_id' => $d->ref_subject_priority_id,
                        'SubjectPriorityIcon' => $d->SubjectPriorityIcon
                    ]
                );
            }
        }

        $s = $this->PDO->prepare(
            'SELECT * from ref_subject_priority where ref_subject_priority_id=:ref_subject_priority_id'
        );
        $s->execute(
            [
                'ref_subject_priority_id' => $d->ref_subject_priority_id
            ]
        );
        $priority = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($priority);

        return true;
    }

    /**
     * Modification du nom d'un role d'une intervention
     * 
     * @return true
     */
    function updateSubjectRole()
    {
        $d = $this->checkParams(
            [
                'ref_subject_role_id' => 'int',
                'role_name' => 'string',
                'order' => 'int'
            ]
        );
        
        // Si le nom et l'ordre n'ont pas changés on ne fait rien
        $s = $this->PDO->prepare(
            'SELECT role_name, `order` from ref_subject_role where 
            ref_subject_role_id=:ref_subject_role_id'
        );
        $s->execute(
            [
                'ref_subject_role_id' => $d->ref_subject_role_id
            ]
        );
        $old_role = $s->fetch(PDO::FETCH_ASSOC);

        if ($old_role['role_name'] == $d->role_name
            && $old_role['order'] == $d->order
        ) {

        } else {
            // On vérifie que le role n'existe pas déjà
            $s = $this->PDO->prepare(
                'SELECT * from ref_subject_role where 
                ref_subject_role_id!=ref_subject_role_id'
            );
            $s->execute(
                [
                    'ref_subject_role_id' => $d->ref_subject_role_id
                ]
            );
            $subject_role_exists = $s->fetch();

            if ($subject_role_exists != false) {
                $this->logError(
                    'Subject role name already exists', 0
                );   
            } else {
                $s = $this->PDO->prepare(
                    'UPDATE ref_subject_role set role_name=:role_name,
                    `order`=:order where ref_subject_role_id=:ref_subject_role_id'
                );
                $s->execute(
                    [
                        'role_name' => $d->role_name,
                        'order' => $d->order,
                        'ref_subject_role_id' => $d->ref_subject_role_id
                    ]
                );
            }
        }

        $s = $this->PDO->prepare(
            'SELECT * from ref_subject_role where ref_subject_role_id=:ref_subject_role_id'
        );
        $s->execute(
            [
                'ref_subject_role_id' => $d->ref_subject_role_id
            ]
        );
        $role = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($role);

        return true;
    }

    /**
     * Modification du nom et de la couleur du status de l'intervention
     * 
     * @return true
     */
    function updateSubjectStatus()
    {
        $d = $this->checkParams(
            [
                'ref_subject_status_id' => 'int',
                'status_name' => 'string',
                'color' => 'string',
                'order' => 'int',
                'is_new' => 'string',
                'is_inprogress' => 'string',
                'is_final' => 'string',
                'icon' => 'string'
            ]
        );
        // error_log($d->is_inprogress);
        // Si le nom, la couleur et l'ordre n'ont pas changé on ne fait rien
        $s = $this->PDO->prepare(
            'SELECT status_name, color, `order`, is_final, is_new, is_inprogress,status_icon from ref_subject_status where
            ref_subject_status_id=:ref_subject_status_id'
        );
        $s->execute(
            [
                'ref_subject_status_id' => $d->ref_subject_status_id
            ]
        );
        $old_status = $s->fetch(PDO::FETCH_ASSOC);

        if ($d->status_name == $old_status['status_name'] 
            && $d->color == $old_status['color'] 
            && $d->order == $old_status['order']
            && $d->is_final == $old_status['is_final']
            && $d->is_new == $old_status['is_new']
            && $d->is_inprogress == $old_status['is_inprogress']
            && $d->icon == $old_status['status_icon']
        ) {
            // le nom et la couleur n'ont pas changé
        } else {
            // On vérifie que le nouveau nom n'existe pas déjà
            $s = $this->PDO->prepare(
                'SELECT * from ref_subject_status where status_name=:status_name
                and ref_subject_status_id!=:ref_subject_status_id'
            );
            $s->execute(
                [
                    'status_name' => $d->status_name,
                    'ref_subject_status_id' => $d->ref_subject_status_id
                ]
            );
            $status_name_exists = $s->fetch();

            if ($status_name_exists != false) {
                $this->logError(
                    'Subject type name already exists', 0
                );
            } else {
                $s = $this->PDO->prepare(
                    'UPDATE ref_subject_status set status_name=:status_name,
                    color=:color, `order`=:order, is_final=:is_final, is_new=:is_new, is_inprogress=:is_inprogress, status_icon=:status_icon where 
                    ref_subject_status_id=:ref_subject_status_id'
                );
                // error_log($d->is_inprogress);
                $s->execute(
                    [
                        'status_name' => $d->status_name,
                        'color' => $d->color,
                        'order' => $d->order,
                        'is_new' => $d->is_new,
                        'is_inprogress' => $d->is_inprogress,
                        'is_final' => $d->is_final,
                        'ref_subject_status_id' => $d->ref_subject_status_id,
                        'status_icon' => $d->icon,
                    ]
                );
            }
        }

        $s = $this->PDO->prepare(
            'SELECT * from ref_subject_status where ref_subject_status_id=:ref_subject_status_id'
        );
        $s->execute(
            [
                'ref_subject_status_id' => $d->ref_subject_status_id
            ]
        );
        $status = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($status);

        return true;
    }

    /**
     * Modification du nom et de la couleur du type de tache de l'intervention
     * 
     * @return true
     */
    function updateSubjectTaskType()
    {
        $d = $this->checkParams(
            [
                'ref_task_type_id' => 'int',
                'task_type_name' => 'string',
                'color' => 'string',
                'order' => 'int',
                'type_prefix' => 'string',
                'default_estimation' => 'float',
                'delay_finish_to_delivery' => 'int',
                'default_effort_by_day' => 'int'
            ]
        );
        // error_log($d->default_effort_by_day);
        $s = $this->PDO->prepare(
            'SELECT task_type_name, color, `order`, type_prefix, default_estimation, delay_finish_to_delivery from ref_task_type 
            where task_type_name=:task_type_name and color=:color and `order`=:order and type_prefix=:type_prefix and default_estimation=:default_estimation
            and delay_finish_to_delivery=:delay_finish_to_delivery and default_effort_by_day=:default_effort_by_day'
        );
        $s->execute(
            [
                'task_type_name' => $d->task_type_name,
                'color' => $d->color,
                'order' => $d->order,
                'type_prefix' => $d->type_prefix,
                'default_estimation' => $d->default_estimation,
                'delay_finish_to_delivery' => $d->delay_finish_to_delivery,
                'default_effort_by_day' => $d->default_effort_by_day
            ]
        );
        $old_task = $s->fetch(PDO::FETCH_ASSOC);

        if ($old_task != false && $d->task_type_name == $old_task['task_type_name']
            && $d->color == $old_task['color']
            && $d->order == $old_task['order']
            && $d->type_prefix == $old_task['type_prefix']
            && $d->default_estimation == $old_task['default_estimation']
            && $d->delay_finish_to_delivery == $old_task['delay_finish_to_delivery']
        ) {
            // rien n'a changé
        } else {
            // On vérifie que le nouveau nom n'existe pas déjà
            $s = $this->PDO->prepare(
                'SELECT * from ref_task_type where task_type_name=:task_type_name
                and ref_task_type_id!=:ref_task_type_id'
            );
            $s->execute(
                [
                    'task_type_name' => $d->task_type_name,
                    'ref_task_type_id' => $d->ref_task_type_id
                ]
            );
            $subject_task_exists = $s->fetch();

            if ($subject_task_exists != false) {
                $this->logError(
                    'Subject task type already exists', 0
                );
            } else {
                $s = $this->PDO->prepare(
                    'UPDATE ref_task_type set task_type_name=:task_type_name, color=:color, `order`=:order, type_prefix=:type_prefix,
                    default_estimation=:default_estimation, delay_finish_to_delivery=:delay_finish_to_delivery, default_effort_by_day=:default_effort_by_day where ref_task_type_id=:task_id'
                );
                $s->execute(
                    [
                        'task_type_name' => $d->task_type_name,
                        'color' => $d->color,
                        'order' => $d->order,
                        'task_id' => $d->ref_task_type_id,
                        'type_prefix' => $d->type_prefix,
                        'default_estimation' => $d->default_estimation,
                        'delay_finish_to_delivery' => $d->delay_finish_to_delivery,
                        'default_effort_by_day' => $d->default_effort_by_day
                    ]
                );
            }
        }

        $s = $this->PDO->prepare(
            'SELECT * from ref_task_type where ref_task_type_id=:ref_task_type_id'
        );
        $s->execute(
            [
                'ref_task_type_id' => $d->ref_task_type_id
            ]
        );
        $type = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($type);

        return true;
    }


    /**
     * Modification d'un status de taches
     * 
     * @return true
     */
    function updateTaskStatus()
    {
        $d = $this->checkParams(
            [
                'ref_task_status_id' => 'int',
                'task_status_name' => 'string',
                'color' => 'string',
                'order' => 'int',
                'icon_class' => 'string',
                'is_final' => 'string',
                'is_new' => 'string',
                'is_inprogress' => 'string'
            ]
        );
        $s = $this->PDO->prepare(
            'SELECT * from ref_task_status where task_status_name=:task_status_name and ref_task_status_id !=:ref_task_status_id'
        );
        $s->execute(
            [
                'task_status_name' => $d->task_status_name,
                'ref_task_status_id' => $d->ref_task_status_id
            ]
        );
        $task_status_exists = $s->fetch();

        if ($task_status_exists != false) {
            $this->logError(
                'Task status already exists', 0
            );
        } else {
            $s = $this->PDO->prepare(
                'UPDATE ref_task_status set task_status_name=:task_status_name, color=:color, `order`=:order, icon_class=:icon_class, is_final=:is_final,
                is_new=:is_new, is_inprogress=:is_inprogress where ref_task_status_id=:ref_task_status_id'
            );
            $s->execute(
                [
                    'task_status_name' => $d->task_status_name,
                    'color' => $d->color,
                    'order' => $d->order,
                    'icon_class' => $d->icon_class,
                    'is_final' => $d->is_final,
                    'is_new' => $d->is_new,
                    'is_inprogress' => $d->is_inprogress,
                    'ref_task_status_id' => $d->ref_task_status_id
                ]
            );
        }

        $s = $this->PDO->prepare(
            'SELECT * from ref_task_status where ref_task_status_id=:ref_task_status_id'
        );
        $s->execute(
            [
                'ref_task_status_id' => $d->ref_task_status_id
            ]
        );
        $status = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($status);

        return true;
    }

    /**
     * Modification d'un status de execrequest
     * 
     * @return true
     */
    function updateExecrequestStatus()
    {
        $d = $this->checkParams(
            [
                'ref_execrequest_status_id' => 'int',
                'execrequest_status_name' => 'string',
                'color' => 'string',
                'order' => 'int',
                'is_final' => 'string',
                'is_new' => 'string',
                'is_inprogress' => 'string'
            ]
        );
        $s = $this->PDO->prepare(
            'SELECT * from ref_execrequest_status where execrequest_status_name=:execrequest_status_name and ref_execrequest_status_id !=:ref_execrequest_status_id'
        );
        $s->execute(
            [
                'execrequest_status_name' => $d->execrequest_status_name,
                'ref_execrequest_status_id' => $d->ref_execrequest_status_id
            ]
        );
        $execrequest_status_exists = $s->fetch();

        if ($execrequest_status_exists != false) {
            $this->logError(
                'execrequest status already exists', 0
            );
        } else {
            $s = $this->PDO->prepare(
                'UPDATE ref_execrequest_status set execrequest_status_name=:execrequest_status_name, color=:color, `order`=:order, is_final=:is_final,
                is_new=:is_new, is_inprogress=:is_inprogress where ref_execrequest_status_id=:ref_execrequest_status_id'
            );
            $s->execute(
                [
                    'execrequest_status_name' => $d->execrequest_status_name,
                    'color' => $d->color,
                    'order' => $d->order,
                    'is_final' => $d->is_final,
                    'is_new' => $d->is_new,
                    'is_inprogress' => $d->is_inprogress,
                    'ref_execrequest_status_id' => $d->ref_execrequest_status_id
                ]
            );
        }

        $s = $this->PDO->prepare(
            'SELECT * from ref_execrequest_status where ref_execrequest_status_id=:ref_execrequest_status_id'
        );
        $s->execute(
            [
                'ref_execrequest_status_id' => $d->ref_execrequest_status_id
            ]
        );
        $status = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($status);

        return true;
    }


    /**
     * Modification du nom et de la couleur du statut de la tache
     * 
     * @return true
     */
    function updateSubjectTaskStatus()
    {
        $d = $this->checkParams(
            [
                'ref_task_type_id' => 'int',
                'ref_task_type_status_id' => 'int',
                'task_status_name' => 'string',
                'color' => 'string',
                'order' => 'int'
            ]
        );

        // Si le nom, la couleur et l'ordre n'ont pas changé, on ne fait rien
        $s = $this->PDO->prepare(
            'SELECT task_status_name, color, `order`
            from ref_task_status where ref_task_type_id=
            :ref_task_type_id and ref_task_type_status_id=
            :ref_task_type_status_id'
        );
        $s->execute(
            [
                'ref_task_type_id' => $d->ref_task_type_id,
                'ref_task_type_status_id' => $d->ref_task_type_status_id
            ]
        );
        $old_task_status = $s->fetch(PDO::FETCH_ASSOC);

        if ($old_task_status != false && $d->task_status_name == $old_task_status['task_status_name']
            && $d->color == $old_task_status['color']
            && $d->order == $old_task_status['order']
        ) {
            
        } else {
            // On vérifie que le nouveau nom n'existe pas déjà
            $s = $this->PDO->prepare(
                'SELECT task_status_name from ref_task_status
                where ref_task_type_id=:ref_task_type_id and 
                ref_task_type_status_id!=:ref_task_type_status_id and 
                task_status_name=:task_status_name'
            );
            $s->execute(
                [
                    'ref_task_type_id' => $d->ref_task_type_id,
                    'ref_task_type_status_id' => $d->ref_task_type_status_id,
                    'task_status_name' => $d->task_status_name
                ]
            );
            $task_status_name_exists = $s->fetch();

            if ($task_status_name_exists != false) {
                $this->logError(
                    'Status already exists in this task', 0
                );
            } else {
                $s = $this->PDO->prepare(
                    'UPDATE ref_task_status set task_status_name=
                    :task_status_name, color=:color, `order`=:order
                    where ref_task_type_id=:ref_task_type_id and 
                    ref_task_type_status_id=:ref_task_type_status_id'
                );
                $s->execute(
                    [
                        'task_status_name' => $d->task_status_name,
                        'color' => $d->color,
                        'order' => $d->order,
                        'ref_task_type_id' => $d->ref_task_type_id,
                        'ref_task_type_status_id' => $d->ref_task_type_status_id
                    ]
                );
            }
        }

        $s = $this->PDO->prepare(
            'SELECT * from ref_task_status where ref_task_type_status_id=:ref_task_type_status_id'
        );
        $s->execute(
            [
                'ref_task_type_status_id' => $d->ref_task_type_status_id
            ]
        );
        $status = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($status);

        return true;
    }

    /**
     * Modification du nom du type d'intervention
     * 
     * @return true
     */
    function updateSubjectType() 
    {
        $d = $this->checkParams(
            [
                'ref_subject_type_id' => 'int',
                'type_name' => 'string',
                'order' => 'int',
                'icon' => 'string'
            ]
        );

        // Si le nom n'a pas changé, on ne fait rien
        $s = $this->PDO->prepare(
            'SELECT type_name from ref_subject_type where 
            ref_subject_type_id=:ref_subject_type_id'
        );
        $s->execute(
            [
                'ref_subject_type_id' => $d->ref_subject_type_id
            ]
        );
        $old_name = $s->fetch()[0];

        // On vérifie que le nouveau nom n'existe pas déjà
        $s = $this->PDO->prepare(
            'SELECT * from ref_subject_type where type_name=:type_name and 
            ref_subject_type_id!=:ref_subject_type_id'
        );
        $s->execute(
            [
                'type_name' => $d->type_name,
                'ref_subject_type_id' => $d->ref_subject_type_id
            ]
        );
        $type_name_exists = $s->fetch();

        if ($type_name_exists != false) {
            $this->logError(
                'Subject type name already exists', 0
            );
        } else {
            $s = $this->PDO->prepare(
                'UPDATE ref_subject_type set type_name=:type_name, `order`=:order,type_icon=:type_icon 
                where ref_subject_type_id=:ref_subject_type_id'
            );
            $s->execute(
                [
                    'type_name' => $d->type_name,
                    'order' => $d->order,
                    'ref_subject_type_id' => $d->ref_subject_type_id,
                    'type_icon' => $d->icon
                ]
            );
        }

        $s = $this->PDO->prepare(
            'SELECT * from ref_subject_type where ref_subject_type_id=:ref_subject_type_id'
        );
        $s->execute(
            [
                'ref_subject_type_id' => $d->ref_subject_type_id
            ]
        );
        $subject = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($subject);

        return true;
    }

    /**
     * Modification d'une tache
     * 
     * @return true
     */
    function updateTask()
    {
        $d = $this->checkParams(
            [
                'ei_task_id' => 'int',
                'ref_task_type_id' => 'int',
                'ref_task_type_status_id' => 'int',
                'title' => 'html',
                'description' => 'html',
                'ei_user_id' => 'int',
                'estimation' => 'int',
                'final_cost' => 'int',
                'Task_Subject_Id' => 'int',
                'expected_end' => 'html',
                'expected_start' => 'html'
                
            ]
        );

        // error_log($d->ei_task_id);
        // error_log($d->ref_task_type_id);
        // error_log($d->ref_task_type_status_id);
        // error_log($d->title);
        // error_log($d->description);
        // error_log($d->ei_user_id);
        // error_log($d->estimation);
        // error_log($d->final_cost);
        // error_log($d->Task_Subject_Id);
        // error_log($d->expected_end);
        // error_log($d->expected_start);

        $title = urldecode($d->title);

        $description = '';
        if ($d->description !== 0) {
            $description = urldecode($d->description);
        } else {
            // Si la description est nulle, on a édité la tache depuis la liste de tache d'intervention on récupère donc la description précédente
            $s = $this->PDO->prepare(
                'SELECT description from ei_task where ei_task_id=:ei_task_id'
            );
            $s->execute(
                [
                    'ei_task_id' => $d->ei_task_id
                ]
            );
            $description = $s->fetch()[0];
        }

        $s = $this->PDO->prepare(
            'SELECT t.ei_task_id, tl.object_id as ei_subject_id, t.ref_task_type_id, t.creator_id, t.created_at, rtt.task_type_name, 
            rtt.color as task_type_color, rts.ref_task_status_id,
            rts.task_status_name, rts.color as task_status_color, t.title,
            t.description, u.ei_user_id, u.username, u.picture_path, t.estimation, t.final_cost, t.expected_end, t.expected_start, rts.icon_class, u2.username as creator_name
            from ei_task t
            left outer join ref_task_status rts
            on t.ref_task_status_id=rts.ref_task_status_id
            left outer join ei_task_link tl
            on t.ei_task_id=tl.ei_task_id
            left outer join ref_task_type rtt
            on t.ref_task_type_id=rtt.ref_task_type_id
            left outer join ref_task_type_status rtts
            on t.ref_task_type_id=rtts.ref_task_type_id
            and t.ref_task_status_id=rtts.ref_task_status_id
            left outer join ei_user u
            on t.ei_user_id=u.ei_user_id
            left outer join ei_user u2
            on t.creator_id=u2.ei_user_id
            where tl.ref_object_type_id="SUBJECT"
            and t.ei_task_id=:ei_task_id'
        );
        $s->execute(
            [
                'ei_task_id' => $d->ei_task_id
            ]
        );
        $old_values = $s->fetch(PDO::FETCH_ASSOC);

        $s = $this->PDO->prepare(
            'SELECT ei_user_id from ei_task where ei_task_id=:ei_task_id'
        );

        $s->execute(
            [
                'ei_task_id' => $d->ei_task_id,
            ]
        );

        $user_id = (int)($s->fetch()?:[0])[0];

        $user_open_task = $this->user['ei_user_id'];

        if ($user_id !== $user_open_task) {
            $s = $this->PDO->prepare(
                'UPDATE ei_task set isread=:is_read, read_dttm=NOW() where ei_task_id=:ei_task_id '
            );
            
            $s->execute(
                [
                    'ei_task_id' => $d->ei_task_id,
                    'is_read' => 'N'
                ]
            );
        }

        $s = $this->PDO->prepare(
            'SELECT task_status_name from ref_task_status rts, ref_task_type_status rtts where rtts.ref_task_type_id=:ref_task_type_id and rts.ref_task_status_id=:ref_task_type_status_id'
        );
        $s->execute(
            [
                'ref_task_type_id' => $d->ref_task_type_id,
                'ref_task_type_status_id' => $d->ref_task_type_status_id
            ]
        );
        $task_status_name = $s->fetch(PDO::FETCH_ASSOC);

        $s = $this->PDO->prepare(
            'SELECT username, picture_path from ei_user where ei_user_id=:ei_user_id;'
        );
        $s->execute(
            [
                'ei_user_id' => $d->ei_user_id
            ]
        );
        $ei_user = $s->fetch(PDO::FETCH_ASSOC);

        $status_changed = false;
        $title_changed = false;
        $description_changed = false;
        $user_changed = false;
        $estimation_changed = false;
        $final_cost_changed = false;

        // error_log(json_encode($old_values));
        if ($d->ref_task_type_status_id != $old_values['ref_task_status_id']) {
            $status_changed = true;
            $this->callClass(
                "Task", 
                "addTaskAudit", 
                [
                    'ei_task_id' =>  $d->ei_task_id,
                    'ei_description' => "updated task status from <strong>".$old_values['task_status_name'].'</strong> to <strong>'.$task_status_name['task_status_name'].'</strong>',
                    'element_type' => "TASK",
                    'element_id' =>  $d->ei_task_id,
                    'label' => $title,
                    'action' => "UPDATE"
                ]
            );
        }

        if ($title != $old_values['title']) {
            $title_changed = true;
                $this->callClass(
                    "Task", 
                    "addTaskAudit", 
                    [
                        'ei_task_id' =>  $d->ei_task_id,
                        'ei_description' => "Updated task title from <strong>".$old_values['title'].'</strong> to <strong>'.$title.'</strong>',
                        'element_type' => "TASK",
                        'element_id' =>  $d->ei_task_id,
                        'label' => $title,
                        'action' => "UPDATE"
                    ]
                );
        }

        if ($description != $old_values['description']) {
            $description_changed = true;
            $this->callClass(
                "Task", 
                "addTaskAudit", 
                [
                    'ei_task_id' =>  $d->ei_task_id,
                    'ei_description' => "updated task description",
                    'element_type' => "TASK",
                    'element_id' =>  $d->ei_task_id,
                    'label' => $title,
                    'action' => "UPDATE"
                ]
            );
        }

        if ($d->ei_user_id != $old_values['ei_user_id']) {
            $user_changed = true;
            $this->callClass(
                "Task", 
                "addTaskAudit", 
                [
                    'ei_task_id' =>  $d->ei_task_id,
                    'ei_description' => "Updated task user assigned from ".'<img  class="img_pofil" src="'.$old_values['picture_path'].'">'.$old_values['username']." to ".'<img  class="img_pofil" src="'.$ei_user['picture_path'].'">'.$ei_user['username'],
                    'element_type' => "TASK",
                    'element_id' =>  $d->ei_task_id,
                    'label' => $title,
                    'action' => "UPDATE"
                ]
            );
        }

        if ($d->estimation != $old_values['estimation']) {
            $estimation_changed = true;
            $this->callClass(
                "Task", 
                "addTaskAudit", 
                [
                    'ei_task_id' =>  $d->ei_task_id,
                    'ei_description' => "updated task estimation from <strong>".$old_values['estimation'].'</strong> to <strong>'.$d->estimation.'</strong>',
                    'element_type' => "TASK",
                    'element_id' =>  $d->ei_task_id,
                    'label' => $title,
                    'action' => "UPDATE"
                ]
            );
        }

        if ($d->final_cost != $old_values['final_cost']) {
            $final_cost_changed = true;
            $this->callClass(
                "Task", 
                "addTaskAudit", 
                [
                    'ei_task_id' =>  $d->ei_task_id,
                    'ei_description' => "Updated task final cost from <strong>".$old_values['final_cost'].'</strong> to <strong>'.$d->final_cost.'</strong>',
                    'element_type' => "TASK",
                    'element_id' =>  $d->ei_task_id,
                    'label' => $title,
                    'action' => "UPDATE"
                ]
            );
        }

        if ($d->Task_Subject_Id != $old_values['ei_subject_id'] && $d->Task_Subject_Id != 0) {
            $this->callClass(
                "Subject", 
                "addSubjectAudit", 
                [
                    'ei_subject_id' => $old_values['ei_subject_id'],
                    'ei_description' => "moved Task to <strong>".$d->Task_Subject_Id.'</strong>',
                    'element_type' => "TASK",
                    'element_id' => $d->ei_task_id,
                    'label' => $title,
                    'action' => "REMOVE"
                ]
            );
            $this->callClass(
                "Task", 
                "addTaskAudit", 
                [
                    'ei_task_id' =>  $d->ei_task_id,
                    'ei_description' => "updated task from subject <strong>".$old_values['ei_subject_id'].'</strong> to <strong>'.$d->Task_Subject_Id.'</strong>',
                    'element_type' => "TASK",
                    'element_id' =>  $d->ei_task_id,
                    'label' => $title,
                    'action' => "UPDATE"
                ]
            );
        }

        if ($d->expected_end != $old_values['expected_end']) {
            $status_changed = true;
            $this->callClass(
                "Task", 
                "addTaskAudit", 
                [
                    'ei_task_id' =>  $d->ei_task_id,
                    'ei_description' => "updated task expected end from <strong>".$old_values['expected_end'].'</strong> to <strong>'.$d->expected_end.'</strong>',
                    'element_type' => "TASK",
                    'element_id' =>  $d->ei_task_id,
                    'label' => $title,
                    'action' => "UPDATE"
                ]
            );
        }

        if ($d->expected_start != $old_values['expected_start']) {
            $status_changed = true;
            $this->callClass(
                "Task", 
                "addTaskAudit", 
                [
                    'ei_task_id' =>  $d->ei_task_id,
                    'ei_description' => "updated task expected start from <strong>".$old_values['expected_start'].'</strong> to <strong>'.$d->expected_start.'</strong>',
                    'element_type' => "TASK",
                    'element_id' =>  $d->ei_task_id,
                    'label' => $title,
                    'action' => "UPDATE"
                ]
            );
        }

        $s = $this->PDO->prepare(
            'UPDATE ei_task set ref_task_type_id=:ref_task_type_id, 
            ref_task_status_id=:ref_task_type_status_id,expected_end=:expected_end, expected_start=:expected_start, title=:title,
            description=:description, ei_user_id=:ei_user_id, estimation=:estimation,
            final_cost=:final_cost where ei_task_id=:ei_task_id'
        );

        $s->execute(
            [
                'ref_task_type_id' => $d->ref_task_type_id,
                'ref_task_type_status_id' => $d->ref_task_type_status_id,
                'title' => $title,
                'description' => $description,
                'ei_user_id' => $d->ei_user_id,
                'estimation' => $d->estimation,
                'final_cost' => $d->final_cost,
                'ei_task_id' => $d->ei_task_id,
                'expected_end' => $d->expected_end,
                'expected_start' => $d->expected_start
            ]
        );
        if ($d->Task_Subject_Id != 0) {
            $s = $this->PDO->prepare(
                "UPDATE ei_task_link set object_id=:Task_Subject_Id
                where ref_object_type_id='SUBJECT' and ei_task_id=:ei_task_id and object_id=:old_subject_id"
            );

            $s->execute(
                [
                    'Task_Subject_Id' => $d->Task_Subject_Id,
                    'ei_task_id' => $d->ei_task_id,
                    'old_subject_id' => $old_values['ei_subject_id']
                ]
            );
        }
        
        

        return true;
    }
}