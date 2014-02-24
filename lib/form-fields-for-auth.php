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
            "desc" => "These users have full access to use the application.<br />Depending on the <code>Config.hashFunction</code> you use, you will set the password accordingly.<br />You can use duckduckgo.com to compute <code>md5</code> or <code>sha1</code> hashes. For example, if you password is <code>MY_SECRET_PASSWORD</code> then go to the following URL.<br /><a target=\"_blank\" href=\"https://duckduckgo.com/?q=sha1+MY_SECRET_PASSWORD\">https://duckduckgo.com/?q=sha1+MY_SECRET_PASSWORD</a>",
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
