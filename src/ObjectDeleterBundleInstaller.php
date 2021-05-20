<?php

namespace TorqIT\ObjectDeleterBundle;

use Doctrine\DBAL\Migrations\Version;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Db;
use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;

class ObjectDeleterBundleInstaller extends SettingsStoreAwareInstaller
{
    public function install()
    {
        Db::get()->query('DROP PROCEDURE IF EXISTS `delete_objects`;');

        $proc = <<<EOT

        CREATE PROCEDURE `delete_objects` (
            classId varchar(190),
            rootPath varchar(765)
        )
        BEGIN	
        
            DROP TABLE IF EXISTS delete_obj;
            
            CREATE TEMPORARY TABLE delete_obj (
                o_id INT NOT NULL
            );
            
            -- find all objects to delete
            INSERT INTO delete_obj(o_id)
                SELECT 
                    o_id
                FROM
                    objects
                WHERE
                    o_classId = classId COLLATE utf8mb4_general_ci
                    AND
                    o_path LIKE CONCAT(rootPath, '%');
                    
            -- return all objects being deleted
            SELECT o_id FROM delete_obj;
                                
            -- delete dependencies source
            DELETE 
                dependencies
            FROM 
                dependencies
             JOIN 
                 delete_obj
             ON
                delete_obj.o_id = dependencies.sourceid
                AND
                dependencies.sourcetype = 'object';
                 
            -- delete dependencies target
            DELETE 
                dependencies
            FROM 
                dependencies
            JOIN 
                delete_obj
            ON
                delete_obj.o_id = dependencies.targetid
                AND
                dependencies.targettype = 'object';
              
            -- delete workflows
            DELETE
                element_workflow_state
            FROM
                element_workflow_state
            JOIN
                delete_obj
            ON
                element_workflow_state.cid = delete_obj.o_id
                AND 
                element_workflow_state.ctype = 'object';
                
            -- delete gridconfig_favourites
            DELETE
                gridconfig_favourites
            FROM
                gridconfig_favourites
            JOIN
                delete_obj
            ON
                gridconfig_favourites.objectId = delete_obj.o_id;
                
            -- delete gridconfig_favourites
            DELETE
                gridconfig_favourites
            FROM
                gridconfig_favourites
            JOIN
                delete_obj
            ON
                gridconfig_favourites.objectId = delete_obj.o_id;
                
            -- delete all metadata
            
            IF EXISTS (SELECT * FROM information_schema.tables WHERE TABLE_SCHEMA = database() AND table_name LIKE 'object_metadata_%') THEN
                SELECT @obj_meta := CONCAT('DELETE ', table_name, ' FROM ', table_name, ' JOIN delete_obj ON ', table_name, '.o_id = delete_obj.o_id;') FROM information_schema.tables WHERE TABLE_SCHEMA = database() AND table_name LIKE 'object_metadata_%';
                PREPARE obj_meta_stmt FROM @obj_meta;
                EXECUTE obj_meta_stmt; 
                DEALLOCATE PREPARE obj_meta_stmt;
            END IF;
            
            IF EXISTS (SELECT * FROM information_schema.tables WHERE TABLE_SCHEMA = database() AND table_name LIKE 'object_metadata_%') THEN
                SELECT @obj_meta_dest := CONCAT('DELETE ', table_name, ' FROM ', table_name, ' JOIN delete_obj ON ', table_name, '.dest_id = delete_obj.o_id;') FROM information_schema.tables WHERE TABLE_SCHEMA = database() AND table_name LIKE 'object_metadata_%';
                PREPARE obj_meta_dest_stmt FROM @obj_meta_dest;
                EXECUTE obj_meta_dest_stmt; 
                DEALLOCATE PREPARE obj_meta_dest_stmt;
            END IF;
            
            -- delete object_query
            SET @obj_query = CONCAT('DELETE object_query_',classId,' FROM object_query_',classId,' JOIN delete_obj ON delete_obj.o_id = object_query_',classId,'.oo_id;');
            PREPARE obj_query_stmt FROM @obj_query;
            EXECUTE obj_query_stmt; 
            DEALLOCATE PREPARE obj_query_stmt;
            
            -- object relations destination
            SELECT @obj_relations_dest := CONCAT('DELETE ', table_name, ' FROM ', table_name, ' JOIN delete_obj ON ', table_name, '.dest_id = delete_obj.o_id AND ', table_name, '.type=''object'';') FROM information_schema.tables WHERE TABLE_SCHEMA = database() AND table_name LIKE 'object_relations_%';
            PREPARE obj_relations_dest_stmt FROM @obj_relations_dest;
            EXECUTE obj_relations_dest_stmt; 
            DEALLOCATE PREPARE obj_relations_dest_stmt;
          
            -- delete object_relations    
            SET @obj_relations = CONCAT('DELETE r FROM object_relations_', classId, ' AS r JOIN delete_obj ON delete_obj.o_id = r.src_id;');
            PREPARE obj_relations_stmt FROM @obj_relations;
            EXECUTE obj_relations_stmt; 
            DEALLOCATE PREPARE obj_relations_stmt;
        
            -- delete object_store  
            SET @obj_store = CONCAT('DELETE s FROM object_store_', classId, ' AS s JOIN delete_obj AS o ON o.o_id = s.oo_id;');
            PREPARE obj_store_stmt FROM @obj_store;
            EXECUTE obj_store_stmt; 
            DEALLOCATE PREPARE obj_store_stmt;
            
            -- delete object_url_slugs    
            DELETE
                object_url_slugs
            FROM
                object_url_slugs
            JOIN
                delete_obj
            ON
                object_url_slugs.objectId = delete_obj.o_id;
                
            -- delete properties    
            DELETE
                properties
            FROM
                properties
            JOIN
                delete_obj
            ON
                properties.cid = delete_obj.o_id
                AND
                properties.ctype = 'object';
                
            -- delete schedule_tasks    
            DELETE
                schedule_tasks
            FROM
                schedule_tasks
            JOIN
                delete_obj
            ON
                schedule_tasks.cid = delete_obj.o_id
                AND
                schedule_tasks.ctype = 'object';
                
            -- delete tags_assignment    
            DELETE
                tags_assignment
            FROM
                tags_assignment
            JOIN
                delete_obj
            ON
                tags_assignment.cid = delete_obj.o_id
                AND
                tags_assignment.ctype = 'object';
                
            -- delete users_workspaces_object    
            DELETE
                users_workspaces_object
            FROM
                users_workspaces_object
            JOIN
                delete_obj
            ON
                users_workspaces_object.cid = delete_obj.o_id;

            -- delete versions    
            DELETE
                versions
            FROM
                versions
            JOIN
                delete_obj
            ON
                versions.cid = delete_obj.o_id
                AND
                versions.ctype = 'object';
                            
            -- delete objects 
            DELETE
                objects
            FROM
                objects
            JOIN
                delete_obj
            ON
                objects.o_id = delete_obj.o_id;		
                
        END
        
        EOT;
        Db::get()->query($proc);

        parent::install();

        return true;
    }

    public function uninstall()
    {
        Db::get()->query('DROP PROCEDURE IF EXISTS `delete_objects`;');

        parent::uninstall();

        return true;
    }
}
