<?php
if (!isset($_POST['created_at'])) {
    ?>
    <div>
        <form action="" method="post">
            <div>
                <label>Created At</label>
                <input type="text" name="created_at"/>
            </div>
            <div>
                <input type="submit" name="submit" value="get new data">
            </div>
        </form>
    </div>
<?php
} else {

    $host = '127.0.0.1';
    $user = 'root';
    $password = 'giang';
    $db = 'farm_14_org_281114';


    mysql_connect($host, $user, $password);
    mysql_select_db($db);

    //$flagValue = '2014-10-01 04:01:55';
    $flagValue = $_POST['created_at'];
    //check datetime format
    if (!preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/", $flagValue)) {
        echo 'Pls enter correct format datetime';
        exit;
    }
    $arrayTable = array(
        'customer_entity',
        'customer_entity_datetime' => array(
            'entity_id',
            'customer_entity',
        ),
        'customer_entity_decimal' => array(
            'entity_id',
            'customer_entity',
        ),
        'customer_entity_int' => array(
            'entity_id',
            'customer_entity',
        ),
        'customer_entity_text' => array(
            'entity_id',
            'customer_entity',
        ),
        'customer_entity_varchar' => array(
            'entity_id',
            'customer_entity',
        ),
        'customer_address_entity',
        'customer_address_entity_datetime' => array(
            'entity_id',
            'customer_address_entity',
        ),
        'customer_address_entity_decimal' => array(
            'entity_id',
            'customer_address_entity',
        ),
        'customer_address_entity_int' => array(
            'entity_id',
            'customer_address_entity',
        ),
        'customer_address_entity_text' => array(
            'entity_id',
            'customer_address_entity',
        ),
        'customer_address_entity_varchar' => array(
            'entity_id',
            'customer_address_entity',
        ),

        'sales_flat_quote',
        'sales_flat_quote_payment' => array(
            'quote_id' => 'entity_id',
            'sales_flat_quote',
        ),
        'sales_flat_quote_item',
        'sales_flat_quote_item_option' => array(
            'item_id',
            'sales_flat_quote_item',
        ),

        'sales_flat_quote_address',
        'sales_flat_quote_shipping_rate' => array(
            'address_id',
            'sales_flat_quote_address',
        ),
        'sales_flat_quote_address_item',


        'sales_flat_order',
        'sales_flat_order_address' => array(
            'parent_id' => 'entity_id',
            'sales_flat_order',
        ),
        'sales_flat_order_payment' => array(
            'parent_id' => 'entity_id',
            'sales_flat_order',
        ),

        'sales_flat_order_grid' => array(
            'entity_id',
            'sales_flat_order',
        ),
        'sales_order_tax' => array(
            'order_id' => 'entity_id',
            'sales_flat_order',
        ),
        'sales_flat_order_item',
        'sales_flat_order_status_history',
        'sales_flat_invoice',
        'sales_flat_invoice_item' => array(
            'parent_id' => 'entity_id',
            'sales_flat_invoice',
        ),
        'sales_flat_invoice_grid',
        'sales_flat_invoice_comment',
        'sales_flat_shipment',
        'sales_flat_shipment_item' => array(
            'parent_id' => 'entity_id',
            'sales_flat_shipment',
        ),
        'sales_flat_shipment_grid',
        'sales_flat_shipment_comment',
        'sales_flat_shipment_track',
        'sales_flat_creditmemo',
        'sales_flat_creditmemo_item' => array(
            'parent_id' => 'entity_id',
            'sales_flat_creditmemo',
        ),
        'sales_flat_creditmemo_grid',
        'sales_flat_creditmemo_comment',
        'sales_recurring_profile',
        'sales_recurring_profile_order' => array(
            'profile_id',
            'sales_recurring_profile',
        ),
        'sales_payment_transaction',

        'sales_order_aggregated_created' => array(
            'custom_change_flag' => 'period',
        ),
        'sales_shipping_aggregated' => array(
            'custom_change_flag' => 'period',
        ),
        'sales_shipping_aggregated_order' => array(
            'custom_change_flag' => 'period',
        ),
        'sales_invoiced_aggregated' => array(
            'custom_change_flag' => 'period',
        ),
        'sales_invoiced_aggregated_order' => array(
            'custom_change_flag' => 'period',
        ),
        'sales_refunded_aggregated' => array(
            'custom_change_flag' => 'period',
        ),
        'sales_refunded_aggregated_order' => array(
            'custom_change_flag' => 'period',
        ),

        'catalog_product_entity',
        'catalog_product_entity_datetime' => array(
            'entity_id',
            'catalog_product_entity',
        ),
        'catalog_product_entity_decimal' => array(
            'entity_id',
            'catalog_product_entity',
        ),
        'catalog_product_entity_gallery' => array(
            'entity_id',
            'catalog_product_entity',
        ),
        'catalog_product_entity_int' => array(
            'entity_id',
            'catalog_product_entity',
        ),
        'catalog_product_entity_media_gallery' => array(
            'entity_id',
            'catalog_product_entity',
        ),
        'catalog_product_entity_media_gallery_value' => array(
            'value_id',
            'catalog_product_entity_media_gallery' => array(
                'entity_id',
                'catalog_product_entity'
            ),
        ),
        'catalog_product_entity_text' => array(
            'entity_id',
            'catalog_product_entity',
        ),
        'catalog_product_entity_tier_price' => array(
            'entity_id',
            'catalog_product_entity',
        ),
        'catalog_product_entity_varchar' => array(
            'entity_id',
            'catalog_product_entity',
        ),
        'catalog_product_bundle_option' => array(
            'parent_id' => 'entity_id',
            'catalog_product_entity',
        ),
        'catalog_product_bundle_option_value' => array(
            'option_id',
            'catalog_product_bundle_option' => array(
                'parent_id' => 'entity_id',
                'catalog_product_entity',
            ),
        ),
        'catalog_product_bundle_selection' => array(
            'primary_key' => 'selection_id',
            'product_id' => 'entity_id',
            'parent_product_id' => 'entity_id',
            'catalog_product_entity',
        ),
        'catalog_product_link' => array(
            'primary_key' => 'link_id',
            'linked_product_id' => 'entity_id',
            'product_id' => 'entity_id',
            'catalog_product_entity',
        ),
        'catalog_product_link_attribute_decimal' => array(
            'primary_key' => 'link_id',
            'link_id',
            'catalog_product_link' => array(
                'linked_product_id' => 'entity_id',
                'product_id' => 'entity_id',
                'catalog_product_entity',
            ),
        ),
        'catalog_product_link_attribute_int' => array(
            'primary_key' => 'link_id',
            'link_id',
            'catalog_product_link' => array(
                'linked_product_id' => 'entity_id',
                'product_id' => 'entity_id',
                'catalog_product_entity',
            ),
        ),
        'catalog_product_link_attribute_varchar' => array(
            'primary_key' => 'link_id',
            'link_id',
            'catalog_product_link' => array(
                'linked_product_id' => 'entity_id',
                'product_id' => 'entity_id',
                'catalog_product_entity',
            ),
        ),
        'catalog_product_option' => array(
            'product_id' => 'entity_id',
            'catalog_product_entity',
        ),
        'catalog_product_option_price' => array(
            'option_id',
            'catalog_product_option' => array(
                'product_id' => 'entity_id',
                'catalog_product_entity',
            ),
        ),
        'catalog_product_option_title' => array(
            'option_id',
            'catalog_product_option' => array(
                'product_id' => 'entity_id',
                'catalog_product_entity',
            ),
        ),
        'catalog_product_option_type_value' => array(
            'option_id',
            'catalog_product_option' => array(
                'product_id' => 'entity_id',
                'catalog_product_entity',
            ),
        ),
        'catalog_product_option_type_price' => array(
            'option_type_id',
            'catalog_product_option_type_value' => array(
                'option_id',
                'catalog_product_option' => array(
                    'product_id' => 'entity_id',
                    'catalog_product_entity',
                ),
            ),
        ),
        'catalog_product_option_type_title' => array(
            'option_type_id',
            'catalog_product_option_type_value' => array(
                'option_id',
                'catalog_product_option' => array(
                    'product_id' => 'entity_id',
                    'catalog_product_entity',
                ),
            ),
        ),
        'catalog_product_super_attribute' => array(
            'product_id' => 'entity_id',
            'catalog_product_entity',
        ),
        'catalog_product_super_attribute_label' => array(
            'product_super_attribute_id',
            'catalog_product_super_attribute' => array(
                'product_id' => 'entity_id',
                'catalog_product_entity',
            ),
        ),
        'catalog_product_super_attribute_pricing' => array(
            'product_super_attribute_id',
            'catalog_product_super_attribute' => array(
                'product_id' => 'entity_id',
                'catalog_product_entity',
            ),
        ),
        'catalog_product_super_link' => array(
            'primary_key' => 'link_id',
            'product_id' => 'entity_id',
            'parent_id' => 'entity_id',
            'catalog_product_entity',),
        'catalog_product_website' => array(
            'product_id' => 'entity_id',
            'catalog_product_entity',
        ),
        'catalog_product_enabled_index' => array(
            'product_id' => 'entity_id',
            'catalog_product_entity',
        ),
    );

    /*table change column 1.4 => 1.9*/
    $arrayTableChangeColumn = array(
        'sales_flat_creditmemo' => array(
            'base_shipping_hidden_tax_amount' => 'base_shipping_hidden_tax_amnt',
        ),
        'sales_flat_creditmemo_item' => array(
            'base_weee_tax_applied_row_amount' => 'base_weee_tax_applied_row_amnt',
        ),
        'sales_flat_invoice_item' => array(
            'base_weee_tax_applied_row_amount' => 'base_weee_tax_applied_row_amnt',
        ),
        'sales_flat_order' => array(
            'forced_do_shipment_with_invoice' => 'forced_shipment_with_invoice',
            'payment_authorization_expiration' => 'payment_auth_expiration',
            'base_shipping_hidden_tax_amount' => 'base_shipping_hidden_tax_amnt',
        ),
        'sales_flat_order_item' => array(
            'base_weee_tax_applied_row_amount' => 'base_weee_tax_applied_row_amnt',
        ),
        'sales_flat_quote_item' => array(
            'base_weee_tax_applied_row_amount' => 'base_weee_tax_applied_row_amnt',
        ),
        'sales_flat_quote_address' => array(
            'base_shipping_hidden_tax_amount' => 'base_shipping_hidden_tax_amnt',
        ),
        'sales_flat_shipment_track' => array(
            'number' => 'track_number',
        ),
        'sales_flat_invoice' => array(
            'base_shipping_hidden_tax_amount' => 'base_shipping_hidden_tax_amnt',
        ),
    );


    class GetNew
    {
        private $_flag; // name flag to get new data, - created
        private $_flagUpdated; //name flag to get data updated
        private $_flagValue; //value of flag
        private $_arrayTable; //array table

        private $_countQuery; //count query
        private $_queryResult; // all query result

        private $_insertCombined; //combined insert, default true
        private $_file; //write file, default true
        private $_fileName;
        private $_breakLine; //break each query
        private $_foreignCheckNot; // set not check foreingn key, insert head result
        private $_foreignCheck; // set check foreingn key, insert tail result

        private $_changeFlag; // key change flag
        private $_primaryKey; //primary key of table

        private $_changeNameColumn; //name column change from CE14 => CE 19

        public function __construct($arrayTable, $flagValue, $changeNameColumn = array())
        {
            ini_set('memory_limit', '-1'); // increment memory limit for php
            $this->_flag = 'created_at';
            $this->_flagUpdated = 'updated_at';
            $this->_flagValue = $flagValue;
            $this->_arrayTable = $arrayTable;

            $this->_countQuery = 0;
            $this->_queryResult = '';

            $this->_insertCombined = false;
            $this->_file = true;
            $flagTem = $this->_flagValue;
            //$this->_fileName = str_replace(array('-', ':', ' '), '_', $flagTem) . '.sql';
            $this->_fileName = 'file_new_db_t.sql';
            $this->_breakLine = $this->_file ? PHP_EOL : '<br/>';
            $this->_foreignCheckNot = $this->_breakLine . 'SET FOREIGN_KEY_CHECKS=0;' . $this->_breakLine;
            $this->_foreignCheck = $this->_breakLine . 'SET FOREIGN_KEY_CHECKS=1;' . $this->_breakLine;

            $this->_changeFlag = 'custom_change_flag';
            $this->_primaryKey = 'primary_key';
            $this->_changeNameColumn = $changeNameColumn;
        }

        /**
         * result(): get result new data
         */
        public function result()
        {
            $this->_queryResult = '';
            $this->_queryResult .= $this->_foreignCheckNot;
            $this->execGetNewData();
            $this->_queryResult .= $this->_foreignCheck;
            $this->writeFileResult();
        }

        /**
         * execGetNewData(): exec get new data
         *
         * add a query insert into maintable
         */
        public function execGetNewData()
        {
            foreach ($this->_arrayTable as $tableMain => $arrayForeigns) {
                if (is_numeric($tableMain)) {
                    $tableMain = $arrayForeigns;
                }

                $query = "select {$tableMain}.* from {$tableMain} ";
                $flag = null;
                $primaryKey = null;
                $tableFlag = $tableMain;
                if (count($arrayForeigns) > 0 && is_array($arrayForeigns)) {
                    if (isset($arrayForeigns[$this->_changeFlag])) {
                        $flag = $arrayForeigns[$this->_changeFlag];
                        unset($arrayForeigns[$this->_changeFlag]);
                    }
                    if (isset($arrayForeigns[$this->_primaryKey])) {
                        $primaryKey = $arrayForeigns[$this->_primaryKey];
                        unset($arrayForeigns[$this->_primaryKey]);
                    }
                    if (count($arrayForeigns) > 1) {
                        $query .= $this->joinTable($tableMain, $arrayForeigns, $tableFlag);
                    }
                }
                $query .= ' where ';
                $queryInsert = $query . $this->getQueryFlagInsert($tableFlag, $flag);
                $issetFlagUpdate = $this->issetFlagUpdate($tableFlag); /* check exists column updated in table*/
                if ($issetFlagUpdate) {
                    $queryUpdate = $query . $this->getQueryFlagUpdate($tableFlag, $flag);
                }
                if ($primaryKey) {
                    $queryGroupBy = " GROUP BY {$tableMain}.{$primaryKey} ";
                    $queryInsert .= $queryGroupBy;
                    $queryUpdate .= $queryGroupBy;
                }

                $this->_queryResult .= $this->getQueryInsert($tableMain, $queryInsert);
                if ($issetFlagUpdate) {
                    $this->_queryResult .= $this->getQueryUpdate($tableMain, $queryUpdate);
                }
            }
        }

        /**
         * joinTable(): exec join another tables, function recursive
         *
         * @param $tableChild : name table main join another tables
         * @param $arrayForeigns : all information foreign(join)
         * @param $tableFlag : name table use flag
         * @return string: strings join
         */
        public function joinTable($tableChild, $arrayForeigns, &$tableFlag)
        {
            $query = '';
            $nameForeigns = array_slice($arrayForeigns, 0, -1);
            $tableParentArray = array_slice($arrayForeigns, -1, 1);
            $tableParent = key($tableParentArray);
            $tableFlag = $tableParent;
            if (is_numeric($tableParent)) {
                $tableParent = $tableParentArray[$tableParent];
                $tableFlag = $tableParent;
                $query .= $this->queryJoinQuery($tableChild, $nameForeigns, $tableParent);
            } else {
                $query .= $this->queryJoinQuery($tableChild, $nameForeigns, $tableParent);
                if (count($tableParentArray[$tableParent]) > 1) {
                    $query .= $this->joinTable($tableParent, $tableParentArray[$tableParent], $tableFlag);
                }
            }
            return $query;
        }

        /**
         * queryJoinQuery(): get query join table
         *
         * @param $tableChild : table main join a table
         * @param $nameForeigns : all information foreign(join)
         * @param $tableParent : table be join
         * @return string: a query join
         */
        public function queryJoinQuery($tableChild, $nameForeigns, $tableParent)
        {
            $query = " join {$tableParent} ON ";
            foreach ($nameForeigns as $nameChild => $nameParent) {
                if (is_numeric($nameChild)) {
                    $nameChild = $nameParent;
                }
                $query .= " {$tableChild}.{$nameChild} = {$tableParent}.{$nameParent} OR ";
            }
            $query = substr($query, 0, -3);
            return $query;
        }

        /**
         * getQueryInsert(): get a query insert
         *
         * @param $table : table main insert
         * @param $query : query select
         * @return string: a query insert
         */
        public function getQueryInsert($table, $query)
        {
            $string = '';
            $result = mysql_query($query);
            if (mysql_num_rows($result)) {
                $allField = $this->getAllFieldsInsert($table);
                if ($this->_insertCombined) {
                    $string .= "insert into {$table} (" . $allField . ") VALUES ";
                    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                        $string .= $this->getStringValues($row);
                        $string .= ',';
                        $this->_countQuery++;
                    }
                    $string = substr($string, 0, -1);
                    $string .= ';' . $this->_breakLine;
                } else {
                    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                        $string .= "insert into {$table} (" . $allField . ") VALUES ";
                        $string .= $this->getStringValues($row);
                        $string .= ';' . $this->_breakLine;
                        $this->_countQuery++;
                    }
                }
            }
            return $string;
        }

        /**
         * getQueryUpdate(): query update table
         *
         * @param $table : table update
         * @param $query : query select
         * @return string: Query UPDATE
         */
        public function getQueryUpdate($table, $query)
        {
            $string = '';
            $result = mysql_query($query);
            if (mysql_num_rows($result)) {
                while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                    $string .= "UPDATE {$table} SET " . $this->queryUpdateSet($row,$table);
                    $string .= ';' . $this->_breakLine;
                    $this->_countQuery++;
                }
            }
            return $string;
        }

        /**
         * queryUpdateSet(): query set in query UPDATE
         *
         * @param $row : result 1 row in query select
         * @return string: query set
         */
        public function queryUpdateSet($row, $table = null)
        {
            $primaryKey = null; /*query where*/
            $query = '';
            foreach ($row as $column => $value) {
                $columnName = $column;
                if($table) {
                    if($this->_changeNameColumn) {
                        if(array_key_exists($table,$this->_changeNameColumn)) {/*exists table change column?*/
                            if(array_key_exists($columnName,$this->_changeNameColumn[$table])) {
                                $columnName = $this->_changeNameColumn[$table][$columnName];
                            }
                        }
                    }
                }
                if (!$primaryKey) {
                    $primaryKey = "{$columnName}='" . addslashes($value) . "'";
                }
                $query .= "{$columnName}='" . addslashes($value) . "',";
            }
            $query = substr($query, 0, -1);
            $query .= ' where ' . $primaryKey;
            return $query;
        }

        /**
         * getQueryFlag(): query flag, in query where
         *
         * @param null $table : table use flag
         * @param null $flag : flag name
         * @param null $flagValue : flag value
         * @return string : query where flag
         */
        public function getQueryFlagInsert($table = null, $flag = null, $flagValue = null)
        {
            $flagCreate = $flag ? $flag : $this->_flag;
            $flagValueCreate = $flagValue ? $flagValue : $this->_flagValue;
            $query = ' ';
            $tableQuery = '';
            if ($table) {
                $tableQuery = $table . '.';
            }
            $query .= "{$tableQuery}{$flagCreate} >= '{$flagValueCreate}' ";
            return $query;
        }

        /**
         * issetFlagUpdate(): check isset column updated_at in table
         *
         * @param $tableFlag
         * @return bool
         */
        public function issetFlagUpdate($tableFlag)
        {
            $query = "SHOW COLUMNS FROM {$tableFlag}";
            $result = mysql_query($query);
            while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
                if ($row[0] == $this->_flagUpdated) {
                    return true;
                }
            }
            return false;
        }

        /**
         * getQueryFlagUpdate(): get query where in query updated
         *
         * @param null $table
         * @param null $flag
         * @param null $flagValue
         * @return string
         */
        public function getQueryFlagUpdate($table = null, $flag = null, $flagValue = null)
        {
            $flagUpdated = $flag ? $flag : $this->_flagUpdated;
            $flagValue = $flagValue ? $flagValue : $this->_flagValue;
            $query = ' ';
            $tableQuery = '';
            if ($table) {
                $tableQuery = $table . '.';
            }
            $query .= "{$tableQuery}{$flagUpdated} >= '{$flagValue}' ";
            //$query .= " and {$tableQuery}" . $this->_flag . " < '{$flagValue}' ";
            return $query;
        }

        /**
         * getStringValues(): get string all value of a row
         *
         * @param $row : result of query
         * @return string
         */
        public function getStringValues($row)
        {
            $string = '(';
            foreach ($row as $value) {
                $string .= "'" . addslashes($value) . "',";
            }
            $string = substr($string, 0, -1);
            $string .= ')';
            return $string;
        }

        /**
         * getAllFieldsInsert(): get all column to insert into
         *
         * @param $table : table get column
         * @return array|string
         */
        public function getAllFieldsInsert($table)
        {
            $query = "SHOW COLUMNS FROM {$table}";
            $result = mysql_query($query);
            $allField = '';
            while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
                $columnName = $row[0];
                if($this->_changeNameColumn) {
                    if(array_key_exists($table,$this->_changeNameColumn)) {/*exists table change column?*/
                        if(array_key_exists($columnName,$this->_changeNameColumn[$table])) {
                            $columnName = $this->_changeNameColumn[$table][$columnName];
                        }
                    }
                }
                $allField .= $columnName . ',';
            }
            $allField = substr($allField, 0, -1);
            return $allField;
        }

        /**
         * setBreakLine(): set break line after insert query
         *
         * @param $breakLine
         */
        public function setBreakLine($breakLine)
        {
            $this->_breakLine = $breakLine;
        }

        /**
         * writeFileResult(): write file
         */
        public function writeFileResult()
        {
            if ($this->_file) {
                $fileResult = fopen($this->_fileName, 'w');
                fwrite($fileResult, $this->_queryResult);
                fclose($fileResult);
            }
        }

        /**
         * checkFile(): check write
         *
         * @return bool
         */
        public function checkFile()
        {
            return $this->_file;
        }

        /**
         * getFileName(): get file name
         *
         * @return string
         */
        public function getFileName()
        {
            return $this->_fileName;
        }

        public function setFileName($fileName)
        {
            $this->_fileName = $fileName;
        }

        public function setArrayTable($arrayTable)
        {
            $this->_arrayTable = $arrayTable;
        }

        /**
         * getCountQuery(): get count query insert
         *
         * @return int
         */
        public function getCountQuery()
        {
            return $this->_countQuery++;
        }

        /**
         * toString(): print string insert
         */
        public function toString()
        {
            echo '<pre>';
            echo $this->_queryResult;
            echo '</pre>';
        }
    }

    $getNewData = new GetNew($arrayTable, $flagValue, $arrayTableChangeColumn);
    try {
        $getNewData->result();

        echo $getNewData->getCountQuery() . ' query';
        if ($getNewData->checkFile()) {
            echo ', check file ' . $getNewData->getFileName();
        } else {
            echo '<br/>' . $getNewData->toString();
        }
    } catch (Exception $e) {
        echo "<b>Error</b>" . $e->messages();
    }
}//end if else post