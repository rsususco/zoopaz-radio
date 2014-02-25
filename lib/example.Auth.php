<?php

/*
Copyright 2014 Weldon Sams

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

class Auth {
    // The maximum number of tries a user has to login before being locked out.
    public $maxTries = 100;

    // Set your users here.
    // Depending on the Config.hashFunction you use, you will set the password
    // accordingly.
    // You can use duckduckgo.com to compute md5 or sha1 hashes. For example,
    // If you password is MY_SECRET_PASSWORD then go to the following URL.
    // https://duckduckgo.com/?q=sha1+MY_SECRET_PASSWORD
    public $users = array("user1"=>"6322c48be847940f6d9466bf07d2ce53186ea77c", "user2"=>"3da541559918a808c2402bba5012f6c60b27661c");

    // These users are restricted in some manner by Streams.isRestricted($file).
    // Currently it prevents audio purchased from iTunes or Amazon from being served
    // to these users. Set to a key (username) from the $users array.
    public $restrictedUsers = array("user2");

    // start: login
    public $year;
    public $month;
    public $day;
    public $hour;
    public $minute;
    public $seconds;

    public $disabled = "";
    public $message = "";
    public $sessionDir = "";
    public $is_logged_in = false;
    public $tries = 0;
    public $userDir = "";
    public $username = "";
    public $currentPlaylist = null;
    public $currentPlaylistDir = null;

    function __construct() {
        $this->year = date("Y");
        $this->month = date("m");
        $this->day = date("d");
        $this->hour = date("H");
        $this->minute = date("i");
        $this->seconds = date("s");
        // Create session directories
        if (!file_exists("sessions")) {
            mkdir("sessions");
        }
        if (!file_exists("sessions/logins")) {
            mkdir("sessions/logins");
        }
        if (!file_exists("sessions/logins/{$this->year}")) {
            mkdir("sessions/logins/{$this->year}");
        }
        if (!file_exists("sessions/logins/{$this->year}/{$this->month}")) {
            mkdir("sessions/logins/{$this->year}/{$this->month}");
        }
        if (!file_exists("sessions/logins/{$this->year}/{$this->month}/{$this->day}")) {
            mkdir("sessions/logins/{$this->year}/{$this->month}/{$this->day}");
        }
        $this->sessionDir = "sessions/logins/{$this->year}/{$this->month}/{$this->day}/{$this->sessid}";
        if (!file_exists($this->sessionDir)) {
            mkdir($this->sessionDir);
        }
    }
}
