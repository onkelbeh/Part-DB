<?php
/**
 * Created by PhpStorm.
 * User: janhb
 * Date: 20.09.2017
 * Time: 20:23
 */

namespace PartDB\Permissions;


class UserPermission extends BasePermission
{
    const CREATE = "create";
    const READ  = "read";
    const EDIT_USERNAME = "edit_username";
    const EDIT_INFOS  = "edit_infos";
    const CHANGE_GROUP  = "change_group";
    const DELETE = "delete";
    const EDIT_PERMISSIONS = "edit_permissions";
    const SET_PASSWORD   = "set_password";

    /**
     * Returns an array of all available operations for this Permission.
     * @return array All availabel operations.
     */
    public static function listOperations()
    {
        /**
         * Dont change these definitions, because it would break compatibility with older database.
         * However you can add other definitions, the return value can get high as 30, as the DB uses a 32bit integer.
         */
        $operations = array();
        $operations[] = static::buildOperationArray(0, static::READ, _("Anzeigen"));
        $operations[] = static::buildOperationArray(4, static::CREATE, _("Anlegen"));
        $operations[] = static::buildOperationArray(8, static::DELETE, _("Löschen"));
        $operations[] = static::buildOperationArray(2, static::EDIT_USERNAME, _("Nutzernamen ändern"));
        $operations[] = static::buildOperationArray(6, static::CHANGE_GROUP, _("Gruppe ändern"));
        $operations[] = static::buildOperationArray(10, static::EDIT_INFOS, _("Informationen ändern"));
        $operations[] = static::buildOperationArray(12, static::EDIT_PERMISSIONS, _("Berechtigungen ändern"));
        $operations[] = static::buildOperationArray(14, static::SET_PASSWORD, _("Password setzen"));

        return $operations;
    }

    protected function modifyValueBeforeSetting($operation, $new_value, $data)
    {
        //Set read permission, too, when you get edit permissions.
        if (($operation == static::EDIT_USERNAME
                || $operation == static::DELETE
                || $operation == static::CHANGE_GROUP
                || $operation == static::CREATE
                || $operation == static::EDIT_INFOS
                || $operation == static::EDIT_USERNAME
                || $operation == static::SET_PASSWORD
                || $operation == static::EDIT_PERMISSIONS)
            && $new_value == static::ALLOW) {
            return parent::writeBitPair($data, static::opToBitN(static::READ), static::ALLOW);
        }

        return $data;
    }
}