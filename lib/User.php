<?php
/*
    part-db version 0.1
    Copyright (C) 2005 Christoph Lechner
    http://www.cl-projects.de/

    part-db version 0.2+
    Copyright (C) 2009 K. Jacobs and others (see authors.php)
    http://code.google.com/p/part-db/

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

namespace PartDB;

/**
 * @todo
 *   Soll der SysAdmin einen Datenbankeintrag haben? Mit Admin-Gruppe?
 *   Oder sollen die Rechte des Admins hardgecoded sein (ID = 0) (wie bei "StructuralDBElement")?
 *   Zweiteres wäre theoretisch schöner, da man die Adminrechte nicht verlieren kann durch eine
 *   kaputte Datenbank. Allerdings müsste das Admin-Passwort dann irgendwo gespeichert werden,
 *   wo man es auch bequem wieder ändern kann, vielleicht in $config (config.php)?
 *   Da momentan andere Sachen eine höhere Priorität haben als die Benutzerverwaltung,
 *   lasse ich das hier einfach mal so stehen, das kann man dann anschauen sobald es gebraucht wird.
 *   kami89
 */

use Exception;
use PartDB\Interfaces\ISearchable;

/**
 * @file User.php
 * @brief class User
 *
 * @class User
 * All elements of this class are stored in the database table "users".
 * @author kami89
 */
class User extends Base\NamedDBElement implements ISearchable
{
    /********************************************************************************
     *
     *   Calculated Attributes
     *
     *   Calculated attributes will be NULL until they are requested for first time (to save CPU time)!
     *   After changing an element attribute, all calculated data will be NULLed again.
     *   So: the calculated data will be cached.
     *
     *********************************************************************************/

    /** @var Group the group of this user */
    private $group = null;

    /********************************************************************************
     *
     *   Constructor / Destructor / reset_attributes()
     *
     *********************************************************************************/

    /**
     * Constructor
     *
     * @param Database      &$database      reference to the Database-object
     * @param User|NULL     &$current_user  @li reference to the current user which is logged in
     *                                      @li NULL if $id is the ID of the current user
     * @param Log           &$log           reference to the Log-object
     * @param integer       $id             ID of the user we want to get
     *
     * @throws Exception    if there is no such user in the database
     * @throws Exception    if there was an error
     */
    public function __construct(&$database, &$current_user, &$log, $id, $data = 1)
    {
        if (! is_object($current_user)) {     // this is that you can create an User-instance for first time
            $current_user = $this;
        }           // --> which one was first: the egg or the chicken? :-)

        parent::__construct($database, $current_user, $log, 'users', $id, $data);
    }

    /**
     * @copydoc DBElement::reset_attributes()
     */
    public function resetAttributes($all = false)
    {
        $this->group = null;

        parent::resetAttributes($all);
    }

    /********************************************************************************
     *
     *   Getters
     *
     *********************************************************************************/

    /**
     * Get the group of this user
     *
     * @return Group        the group of this user
     *
     * @throws Exception    if there was an error
     */
    public function getGroup()
    {
        if (! is_object($this->group)) {
            $this->group = new Group(
                $this->database,
                $this->current_user,
                $this->log,
                $this->db_data['group_id']
            );
        }

        return $this->group;
    }

    /**
     * Gets the username of the User.
     * @return string The username.
     */
    public function getName()
    {
        return $this->db_data['name'];
    }

    /**
     * Gets the first name of the user.
     * @return string The first name.
     */
    public function getFirstName()
    {
        return $this->db_data['first_name'];
    }

    /**
     * Gets the last name of the user.
     * @return string The first name.
     */
    public function getLastName()
    {
        return $this->db_data['last_name'];
    }

    /**
     * Gets the email address of the user.
     * @return string The email address.
     */
    public function getEmail()
    {
        return $this->db_data['last_name'];
    }

    /**
     * Gets the department of the user.
     * @return string The department of the user.
     */
    public function getDepartment()
    {
        return $this->db_data['department'];
    }

    /**
     * Checks if a given password, is valid for this account.
     * @param $password string The password which should be checked.
     */
    public function isPasswordValid($password)
    {
        $hash = $this->db_data['password'];
        return password_verify($password, $hash);
    }

    /********************************************************************************
     *
     *   Setters
     *
     *********************************************************************************/

    /**
     * Change the group ID of this user
     *
     * @param integer $new_group_id     the ID of the new group
     *
     * @throws Exception if the new group ID is not valid
     * @throws Exception if there was an error
     */
    public function setGroupID($new_group_id)
    {
        $this->setAttributes(array('group_id' => $new_group_id));
    }

    /**
     * Sets a new password, for the User.
     * @param $new_password string The new password.
     */
    public function setPassword($new_password)
    {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $this->setAttributes(array("password" => $hash));
    }

    /**
     * Set a new first name.
     * @param $new_first_name string The new first name.
     */
    public function setFirstName($new_first_name)
    {
        $this->setAttributes(array('first_name' => $new_first_name));
    }

    /**
     * Set a new first name.
     * @param $new_first_name string The new first name.
     */
    public function setLastName($new_last_name)
    {
        $this->setAttributes(array('last_name' => $new_last_name));
    }


    /********************************************************************************
     *
     *   Static Methods
     *
     *********************************************************************************/

    /**
     * @copydoc DBElement::check_values_validity()
     */
    public static function checkValuesValidity(&$database, &$current_user, &$log, &$values, $is_new, &$element = null)
    {
        // first, we let all parent classes to check the values
        parent::checkValuesValidity($database, $current_user, $log, $values, $is_new, $element);

        // check "group_id"
        try {
            $group = new Group($database, $current_user, $log, $values['group_id']);
        } catch (Exception $e) {
            debug(
                'warning',
                _('Ungültige "group_id": "').$values['group_id'].'"'.
                _("\n\nUrsprüngliche Fehlermeldung: ").$e->getMessage(),
                __FILE__,
                __LINE__,
                __METHOD__
            );
            throw new Exception(_('Die gewählte Gruppe existiert nicht!'));
        }
    }



    /**
     * Get count of users
     *
     * @param Database &$database   reference to the Database-object
     *
     * @return integer              count of users
     *
     * @throws Exception            if there was an error
     */
    public static function getCount(&$database)
    {
        if (!$database instanceof Database) {
            throw new Exception(_('$database ist kein Database-Objekt!'));
        }

        return $database->getCountOfRecords('users');
    }

    /**
     * Search elements by name.
     *
     * @param Database &$database reference to the database object
     * @param User &$current_user reference to the user which is logged in
     * @param Log &$log reference to the Log-object
     * @param string $keyword the search string
     * @param boolean $exact_match @li If true, only records which matches exactly will be returned
     * @li If false, all similar records will be returned
     *
     * @return array    all found elements as a one-dimensional array of objects,
     *                  sorted by their names
     *
     * @throws Exception if there was an error
     */
    public static function search(&$database, &$current_user, &$log, $keyword, $exact_match)
    {
        return parent::searchTable($database, $current_user, $log, "user", $keyword, $exact_match);
    }

    /**
     * @param $database Database
     * @param $username string The username, for which the User should be returned.
     * @return User
     * @throws Exception
     */
    public static function getUserByName(&$database, &$log, $username)
    {
        $username = trim($username);
        $query = 'SELECT * FROM users WHERE name = ?';
        $query_data = $database->query($query, array($username));

        if (count($query_data) > 1)
        {
            throw new Exception("Die Abfrage des Nutzernamens hat mehrere Nutzer ergeben");
        }

        $user_data = $query_data[0];
        $user = null;
        return new User($database, $user, $log, $user_data['id'], $user_data);
    }

    /**
     * Checks if a user is logged in, in the current session.
     * @return boolean true, if a user is logged in.
     */
    public static function isLoggedIn()
    {
        return self::getLoggedInID() > 0;
    }

    /**
     * Gets the id of the currently logged in user.
     * @return int The id of the logged in user, if someone is logged in. Else 0 (anonymous).
     */
    public static function getLoggedInID()
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']<0){
            return 0;   //User anonymous.
        }
        else {
            return $_SESSION['user'];
        }
    }

    /**
     * Gets a user instance for the currently logged in user.
     * If nobody is logged in, it will return the anonymous user (id = 0)
     * @param $database Database The Database which should be used for request.
     * @param $log Log The Log, which should be used.
     * @return User The user, which is currently logged in.
     */
    public static function getLoggedInUser(&$database = null, &$log = null)
    {
        if(is_null($database) || is_null($log)) {
            $database = new Database();
            $log = new Log($database);
        }
        $var = null;
        return new User($database, $var, $log, self::getLoggedInID());
    }

    /**
     * Log in the given user for the current session.
     * @param $user User The user which should be logged in.
     * @param $password string When not empty, it will be checked if this password is correct, and only then the user
     * will be logged in.
     * @return boolean True, if the user was successfully logged in. False if a error appeared, like a wrong password.
     */
    public static function login(&$user, $password = "")
    {
        if(!empty($password) && !$user->isPasswordValid($password)) { //If $password is set, and wrong.
            return false;
        }
        $_SESSION['user'] = $user->getID();
        return true;
    }

    /**
     * Log out the current user and set logged in to anonymous.
     * @return boolean True, if the user was successful logged out.
     */
    public static function logout()
    {
        $_SESSION['user'] = 0;
        return true;
    }
}
