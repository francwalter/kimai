<?php
/**
 * This file is part of
 * Kimai - Open Source Time Tracking // http://www.kimai.org
 * (c) 2006-2009 Kimai-Development-Team
 *
 * Kimai is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; Version 3, 29 June 2007
 *
 * Kimai is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kimai; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// This file was automatically generated by the installer

$server_hostname = "localhost:3305";
$server_database = "kimai";
$server_username = "franc"; // root hat noch das alte 4.0 Passwortsystem und das geht in PHP 5.3 nicht mehr
$server_password = "abcdefghijklM";
// fcw: bei pdo funktioniert das Speichern der User nicht richtig.
// $server_conn     = "mysql"; 
$server_conn     = "pdo";
$server_type     = "mysql";
$server_prefix   = "ew6_";
$language        = "de";

// fcw: das selbe Salz wie auf ew6.org nehmen
$password_salt   = "qkSX9ip5PbX25wisaHXZ7";

?>