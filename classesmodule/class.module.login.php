<?php

    require_once "lib/common/class.sendMail.php";
    
    class login {

        var $action;
        var $input;
        var $output;
        var $commonObj;

        function __construct() {
            $this->commonObj = new commonFunction();
            
            $this->action = "";
            $this->output = array();
        }

        function executeModule() {
            switch($this->action) {
                case 'logincheck':
                    $this->loginAction();
                    break;
                default:
                    $this->defaultAction();
            }
        }

        function loginAction() {

            require_once "classes/class.generalsql.php";
            $generalSqlObj = new generalSql();
            $sql = $generalSqlObj->login($this->input['userName']);
            $result = dbConnection::selectQuery($sql);
            $userTypeId = $result[0]['login_password'];

            if(isset($result[0]['login_password']) && $result[0]['login_password']==$this->input['password']) {
                $this->output['loginStatus'] = "SUCCESS";
                $this->output['userName'] = $result[0]['UserName'];
                $this->output['fullName'] = $result[0]['LongName'];
                $this->output['serverMsg'] = "User checked successfully";
            } else {
                $this->output['loginStatus'] = "NOT SUCCESS";
                $this->output['serverMsg'] = "Something went wrong";
            }

        }

        function defaultAction() {
            $this->output = "I am login module default action :) ".json_encode($this->input);
        }

        function getModuleOutput() {
            return $this->output;
        }

    }

?>