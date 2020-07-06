<?php

class generalSql {

    function login($userName) {

        $sql = "SELECT 
                    aes_decrypt(c.login_password, sha2('newsj',256)) as login_password,
                    u.UserName, 
                    u.LongName 
                FROM
                    users u,
                    credentials c 
                WHERE 
                    u.UserName = '".$userName."' 
                    AND c.r_id = u.id";

        return $sql;

    }

    function newsPoolList($recordId, $noOfRecords) {

        $sql = "SELECT 
                    np.pkey,
                    np.SlugName,
                    np.inmail_id,
                    np.category,
                    np.ctags,
                    np.ntags 
                FROM 
                    news_pool np 
                ORDER BY np.pkey LIMIT ".$recordId.",".$noOfRecords;

        return $sql;

    }

    function categoryView($recordId, $category) {

        $sql = "SELECT 
                    np.ProcessedContent 
                FROM 
                    news_pool np 
                WHERE 
                    np.pkey = ".$recordId." 
                    AND np.Category = '".$category."'";

        return $sql;
        
    }

}

?>