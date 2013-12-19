<?php if (!defined("installer")) { exit(); }

function getFormFieldsForAuth() {
    $formFieldsForAuth = array(
        array(
            "var" => "maxTries",
            "exp" => "10",
            "desc" => "The maximum number of tries to login before being locked out of the application.",
            "isboolean" => false,
            "isusers" => false,
            "isrestrictedusers" => false),
        array(
            "var" => "users",
            "exp" => "",
            "desc" => "These users have full access to use the application.",
            "isboolean" => false,
            "isusers" => true,
            "isrestrictedusers" => false),
        array(
            "var" => "restrictedUsers",
            "exp" => "",
            "desc" => "These users are prevented from listening to restricted content that has been purchased from iTunes or Amazon.",
            "isboolean" => false,
            "isusers" => false,
            "isrestrictedusers" => true),
    );
    return $formFieldsForAuth;
}
