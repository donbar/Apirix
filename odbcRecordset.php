<?php
class odbcRecordset {
   var $recordcount;
   var $currentrow;
   var $eof;

   var $recorddata;
   var $query;

   function odbcConnection(){
      $this->recordcount = 0;
      $this->recorddata = 0;
   }

   function SetData( $newdata, $num_records, $query ) {
      $this->recorddata = $newdata;
      $this->recordcount = $num_records;
      $this->query = $query;
      $this->currentrow = 0;
      $this->set_eof();
   }

   function set_eof() {
      $this->eof = $this->currentrow >= $this->recordcount;
   }

   function movenext()  { if ($this->currentrow < $this->recordcount) { $this->currentrow++; $this->set_eof(); } }
   function moveprev()  { if ($this->currentrow > 0)                  { $this->currentrow--; $this->set_eof(); } }
   function movefirst() { $this->currentrow = 0; set_eof();                                               }
   function movelast()  { $this->currentrow = $this->recordcount - 1;  set_eof();                         }

   function data($field_name) {
      if (isset($this->recorddata[$this->currentrow][$field_name])) {
         $thisVal = $this->recorddata[$this->currentrow][$field_name];
      } else if ($this->eof) {
         die("<B>Error!</B> eof of recordset was reached");
      } else {
         die("<B>Error!</B> Field <B>" . $field_name . "</B> was not found in the current recordset from query:<br><br>$this->query");
      }

      return $thisVal;
   } 

   function __get($field_name) {
      return $this->data($field_name);
   } 
}

class odbcConnection {
   var $user;  //Username for the database
   var $pass; //Password

   var $conn_handle; //Connection handle
   var $temp_fieldnames; //Tempory array used to store the fieldnames, makes parsing returned data easier.
   
   function odbcConnection(){
      $this->user = "";
      $this->pass = "";
   }
   
   function open($dsn,$user,$pass){
      $handle = @odbc_connect($dsn,$user,$pass,SQL_CUR_USE_ODBC) or
         die("<B>Error!</B> Couldn't Connect To Database. Error Code:  ".odbc_error());
      $this->conn_handle = $handle;
      return true;
   }
   
   function &execute($query){
      //Create a temp recordset
      $newRS = new odbcRecordset;
      $thisData = "";

      $res = @odbc_exec($this->conn_handle,$query) or
         die("<B>Error!</B> Couldn't Run Query:<br><br>" . $query . "<br><br>Error Code:  ".odbc_error());
      unset($this->temp_fieldnames);

      $i = 0;
      $j = 0;
      $num_rows = 0;

      // only populate select queries
      if (stripos($query, 'select ') !== false) {
         while(odbc_fetch_row($res)) {
            $num_rows++;
   
            //Build tempory
            for ($j = 1; $j <= odbc_num_fields($res); $j++) {
               $field_name = odbc_field_name($res, $j);
               $this->temp_fieldnames[$j] = $field_name;
               $ar[$field_name] = odbc_result($res, $field_name) . "";
            }
   
            $thisData[$i] = $ar;
            $i++;
         }
      }
      
      //populate the recordset and return it
      $newRS->SetData( $thisData, $num_rows, $query );
      return $newRS;
   }
}
?>