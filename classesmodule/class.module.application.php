<?php

    require_once "lib/common/class.sendMail.php";
    
    class application {

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
                case 'newsPool':
                    $this->newsPoolAction();
                    break;
                case 'categoryView':
                    $this->categoryViewAction();
                    break;
                default:
                    $this->defaultAction();
            }
        }

        function categoryViewAction() {

            require_once "classes/class.generalsql.php";
            $generalSqlObj = new generalSql();
            $sql = $generalSqlObj->categoryView($this->input['recordId'],$this->input['category']);
            $this->output['categoryViewSql'] = $sql;
            // dbConnection::selectQuery("SET NAMES 'utf8mb4'");
            //$this->output['CHARSET'] = dbConnection::$dbObj->set_charset('utf8mb4');
            $result = dbConnection::selectQuery($sql);        

            if(isset($result[0])) {
            //print_r($result);exit;    
                $this->output['processedContent'] = $result[0]['ProcessedContent'];
                $this->output['userName'] = $this->input['userName'];
                $this->output['serverMsg'] = "Category content retrived successfully";                
            } else {
                $this->output['processedContent'] = "Nil";
                $this->output['userName'] = $this->input['userName'];
                $this->output['serverMsg'] = "Category content not retrived successfully";
            }

        }

        function newsPoolAction() {

            require_once "classes/class.generalsql.php";
            $generalSqlObj = new generalSql();
            //$sql = $generalSqlObj->newsPoolList($this->input['recordIndex'], $this->input['noOfRecords']);
            $sql = $generalSqlObj->newsPoolList($this->input['recordIndex'], 15);
            $this->output['newspoolsql'] = $sql;
            $result = dbConnection::selectQuery($sql);
            
            if(isset($result[0])) {

                $record = array();
                $newsPool = array();
                foreach($result AS $index => $value) {
                    if(!in_array($value['inmail_id'], $record)) {
                        $temp['slugName'] = $value['SlugName'];
                        $temp['slugDate'] = date('F d, Y');
                        $temp['slugLastUpdate'] = date('F d, Y');
                        $temp[$value['category']] = $value['pkey'];
                        $temp['inmailId'] = $value['inmail_id'];
                        $temp['ctags'] = explode(",", $value['ctags']);
                        $temp['ntags'] = explode(",", $value['ntags']);
                        $temp['recordIndex'] = $value['pkey'];

                        array_push($newsPool, $temp);
                        array_push($record, $value['inmail_id']);
                    } else {
                        $elementIndex = array_search($value['inmail_id'], $record);
                        $newsPool[$elementIndex][$value['category']] = $value['pkey'];
                    }
                }

                $this->output['newsPool'] = $newsPool;
                //$this->output['record'] = $record;
                $this->output['userName'] = $this->input['userName'];
                $this->output['serverMsg'] = "News pool list retrived successfully";

            } else {

                $this->output['newsPool'] = "Nil";
                $this->output['userName'] = $this->input['userName'];
                $this->output['serverMsg'] = "News pool list not retrived successfully";                

            }

        }

        function defaultAction() {
            $this->output = "I am defaultAction. I got called successfully :)";
        }

        function getModuleOutput() {
            return $this->output;
        }

    }

?>