<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        使用PDO來處理資料庫。
  @see          http://www.actman.tw/~blog/2010/11/php-%E4%BD%BF%E7%94%A8-pdo-%E5%AD%98%E5%8F%96%E8%B3%87%E6%96%99%E5%BA%AB%E4%B8%80%E5%AD%98%E5%8F%96%E6%AD%A5%E9%A9%9F%E8%88%87%E5%B8%B8%E7%94%A8%E5%8A%9F%E8%83%BD%E4%BB%8B%E7%B4%B9/
  @see          http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html#7.1
  @see          http://www.php.net/manual/zh/pdo.constants.php
  @see          http://wordpress.lamp.dzvhost.com/?p=605
  @see          http://www.dewen.org/q/6676
  @see          http://www.laruence.com/2012/10/16/2831.html
  @see          http://jax-work-archive.blogspot.tw/2013/04/pdo-fetch.html
  @version      1.0.0
  @date         2015-04-14
  @since        1.0.1 -> 加入Get_Lock()、Release_Lock()方法、修正SQL語法有錯誤時的顯示方法。
  @since        1.0.0 -> 新增此新類別。
**/

class Pdo_Driver
{
    /**
    @brief      分頁風格
    **/
    public $paginstyle = 1;

    /**
    @brief      the Master handle.
    **/
    public $handle;

    /**
    @brief      the Slave handle.
    **/
    public $handle_slave;

    /**
    @brief      statement of database.
    **/
    public $stmt;

    /**
    @brief      the Master dsn.
    **/
    private $local;

    /**
    @brief      the Master name of database.
    **/
    private $dbname;

    /**
    @brief      the Master port of database.
    **/
    private $port;

    /**
    @brief      the Master user of database.
    **/
    private $user;

    /**
    @brief      the Master password of database.
    **/
    private $pass;

    /**
    @brief      the Slave dsn.
    **/
    private $local_slave;

    /**
    @brief      the Slave name of database.
    **/
    private $dbname_slave;

    /**
    @brief      the Slave port of database.
    **/
    private $port_slave;

    /**
    @brief      the Slave user of database.
    **/
    private $user_slave;

    /**
    @brief      the Slave password of database.
    **/
    private $pass_slave;

    /**
    @cond       建構子
    @remarks    利用建構子來初始化連線
    **/
    public function __construct()
    {
        # ----------------------------------------------------------------------
        # 檢查環境
        # ----------------------------------------------------------------------
        $this->initDrivers();
        # ----------------------------------------------------------------------
        # 初始化連線
        # ----------------------------------------------------------------------
        $this->init( Bootstart::$_config );
    }
    /**
    @endcond
    **/

    /**
    @brief      檢查當前的環境有沒有 mysql 的 PDO 驅動
    **/
    private function initDrivers()
    {
        # ----------------------------------------------------------------------
        # 取出當前Pdo 支援的資料庫類型
        # ----------------------------------------------------------------------
        $drivers = array_flip( PDO::getAvailableDrivers() );
        # ----------------------------------------------------------------------
        # 如果mysql不存在就提示錯誤
        # ----------------------------------------------------------------------
        if ( ! isset( $drivers['mysql'] ) )
        {
            show_error( 'No drivers : mysql' );
        }
    }

    /**
    @brief      initialize dsn, user and password of database from 'ini'.
    @param      $ini_array 專案的資料庫連線資訊
    **/
    public function init( $ini_array )
    {
        # ----------------------------------------------------------------------
        # Master 連線
        # ----------------------------------------------------------------------
        $this->local  = $ini_array['masterdb']['db_host'];
        $this->user   = $ini_array['masterdb']['db_user'];
        $this->pass   = $ini_array['masterdb']['db_password'];
        $this->dbname = $ini_array['masterdb']['db_database'];
        $this->port   = $ini_array['masterdb']['db_port'];
        # ----------------------------------------------------------------------
        # Slave 連線
        # ----------------------------------------------------------------------
        $this->local_slave  = $ini_array['slavedb']['db_host'];
        $this->user_slave   = $ini_array['slavedb']['db_user'];
        $this->pass_slave   = $ini_array['slavedb']['db_password'];
        $this->dbname_slave = $ini_array['slavedb']['db_database'];
        $this->port_slave   = $ini_array['slavedb']['db_port'];
        # ----------------------------------------------------------------------
        # 處理資料庫連線
        # ----------------------------------------------------------------------
        $this->connect();
    }

    /**
    @brief      在這邊處理資料庫連線
    @param      $_reconnectAll 是否重新連線
    **/
    public function connect( $_reconnectAll = false )
    {
        try
        {
            $this->handle_slave = new PDO( "mysql:host=".$this->local_slave.";port=".$this->port_slave.";dbname=".$this->dbname_slave.";charset=utf8;" , $this->user_slave , $this->pass_slave );
            $this->handle_slave->exec("SET NAMES utf8");
            # ------------------------------------------------------------------
            # 設定PDO 屬性為： 錯誤報告 , 拋出例外
            # ------------------------------------------------------------------
            $this->handle_slave->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch( PDOException $s )
        {
            show_error( "Failed to connect Slave database " .$s->getMessage() );
        }
        # ----------------------------------------------------------------------
        # 如果 MASTER 連現已存在，就不重新連線
        # ----------------------------------------------------------------------
        if ( $this->handle instanceof PDO && ! $_reconnectAll )
        {
            return;
        }
        try
        {
            $this->handle = new PDO( "mysql:host=".$this->local.";port=".$this->port.";dbname=".$this->dbname.";charset=utf8;" , $this->user , $this->pass );
            $this->handle->exec("SET NAMES utf8");
            # ------------------------------------------------------------------
            # 設定PDO 屬性為： 錯誤報告 , 拋出例外
            # ------------------------------------------------------------------
            $this->handle->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch( PDOException $e )
        {
            show_error( "Failed to connect Master database " .$e->getMessage() );
        }
    }

    /**
    @brief      將字串加入引號及反斜線
    @param      String      $var 處理跳脫
    @retval     String
    **/
    public function quote( $var = null )
    {
        if ( is_array( $var ) )
        {
            foreach ( $var as $key => $value )
            {
                $var[$key] = $this->handle->quote( $value );
            }
            return join( ',' , $var );

        }
        elseif ( $var === null )
        {
            return 'NULL';
        }
        else
        {
            return $this->handle->quote( $var );
        }
    }

    /**
    @brief      另外處理 PDO 拋出的異常錯誤
    @param      Object      $exception 錯誤的 Object
    @param      String      $sql 造成錯誤的SQL語法
    @param      Array       $params_array 造成錯誤的SQL語法用的 prepare 參數
    **/
    private function _PDOException( $exception , $sql , $params_array = null )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $tmpStr = '';
        # ----------------------------------------------------------------------
        # 處理錯誤
        # ----------------------------------------------------------------------
        foreach( $exception->getTrace() as $VArr )
        {
            $VArr['file'] = isset( $VArr['file'] ) ? $VArr['file'] : '?';
            $VArr['class'] = isset( $VArr['class'] ) ? $VArr['class'] : '?';
            $VArr['function'] = isset( $VArr['function'] ) ? $VArr['function'] : '?';

            $tmpStr .= 'PDO :' . $VArr['file'] . '|function:' . $VArr['function'] . '|class:' . $VArr['class'];
            if ( isset( $VArr['args'] ) )
            {
                foreach ( $VArr['args'] as $Arr )
                {
                    if ( is_array( $Arr ) )
                    {
                        continue;
                    }
                    else
                    {
                        $tmpStr .= 'Err:' . $Arr;
                    }
                }
            }
        }
        if ( is_array( $params_array ) )
        {
            $tmpStr .= '|SQL:' . $sql . '|SQLParams:' . implode( '&' , $params_array );
        }
        else
        {
            $tmpStr .= '|SQL:' . $sql;
        }
        # ----------------------------------------------------------------------
        # 依照不同的開發模式處理不同錯誤訊息
        # ----------------------------------------------------------------------
        if ( defined( 'DEVIL_APP_ENVIRONMENT' ) )
        {
            switch ( DEVIL_APP_ENVIRONMENT )
            {
                case 'development':
                    # ----------------------------------------------------------
                    # 依照輸出錯誤的設定方式來呈現錯誤
                    # ----------------------------------------------------------
                    switch( DEVIL_APP_DEBUGMETHOD )
                    {
                        # ------------------------------------------------------
                        # 原始方式
                        # ------------------------------------------------------
                        default:
                        case 'showpage':
                            # --------------------------------------------------
                            # 另外處理錯誤的SQL語法
                            # --------------------------------------------------
                            # 有些錯誤不會回傳錯誤續訊息，只會拋 Status Code ( 例如: HY093 )
                            $errorInfo = ( isset( $exception->errorInfo[2] ) ? $exception->errorInfo[2] : 'Unknown Error, SQL State: '.$exception->errorInfo[0] );
                            $getTraceArr = $exception->getTrace();
                            # --------------------------------------------------
                            # 防呆
                            # --------------------------------------------------
                            if ( isset( $getTraceArr[1] ) )
                            {
                                $getTraceArr = $getTraceArr[1];
                                $errorInfo .= '<br /><br />SQL: ';
                                $sql_prepare = self::sql_prepare( $getTraceArr['args'][0] , isset( $getTraceArr['args'][1] ) ? $getTraceArr['args'][1] : array() );
                                $errorInfo .= ( ! isset( $getTraceArr['args'][1] ) ) ? $getTraceArr['args'][0] : ( $sql_prepare ) ? $sql_prepare : $getTraceArr['args'][0];
                            }
                            # --------------------------------------------------
                            # 顯示錯誤
                            # --------------------------------------------------
                            Bootstart::$_lib['Core_Error']->show_php_error
                            (
                                $exception->getCode() ,
                                $errorInfo ,
                                $getTraceArr['file'] ,
                                $getTraceArr['line']
                            );
                            break;
                        # ------------------------------------------------------
                        # 除錯列方式
                        # ------------------------------------------------------
                        case 'debugbar':
                            list ( $Pdo_usec , $Pdo_sec ) = explode( ' ' , microtime() );
                            $data = array_reverse( explode( '#' , $exception->getTraceAsString() ) );
                            $data['errSQLSTATE'] = $exception->getCode();
                            $data['errSQLCODE'] = $exception->errorInfo[1];
                            preg_match( '/near .+|Unknown.+|Table .+/' , $exception->errorInfo[2] , $TempArr );
                            $data['errSQLInfo'] = '<span class="red">' . $TempArr[0] . '</span>';
                            $data['errfile'] = $exception->getFile();
                            $data['errline'] = $exception->getLine();
                            Bootstart::$_debug->addMessage( 'warnings' , 'PDO-' . $Pdo_usec , $data );
                            break;
                    }
                    break;
                case 'testing':
                case 'production':
                    error_log( $tmpStr );
                    break;
                default:
                    Core_preDie( 'The application environment is not set correctly.' );
            }
        }
        else
        {
            error_log( $tmpStr );
        }
    }

    /**
    @brief      當顯示錯誤訊息時產生完整SQL語法用
    @param      String      $sql 包含佔位符號的SQL語句
    @param      Array       $prepare 要替換佔位符號的參數
    @retval     String      SQL語法
    **/
    private function sql_prepare( $sql , $prepare = array() )
    {
        # ----------------------------------------------------------------------
        # 直接返回不處理
        # ----------------------------------------------------------------------
        if ( strpos( $sql , ':' ) !== false && strpos( $sql , '?' ) !== false )
        {
            return false;
        }
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $preg_Str = '';
        # ----------------------------------------------------------------------
        # 如果佔位符號 : 存在 && 有需要填入的參數
        # ----------------------------------------------------------------------
        if ( strpos( $sql , ':' ) !== false && $prepare != false )
        {
            # ------------------------------------------------------------------
            # 依照參數正規化取代
            # ------------------------------------------------------------------
            foreach( $prepare as $key => $val )
            {
                $sql = preg_replace( '/\:(' . $key . ')/' , "'" . $prepare[$key] . "'" , $sql );
            }

            return $preg_Str;
        }
        # ----------------------------------------------------------------------
        # 如果佔位符號 ? 存在 && 有需要填入的參數
        # ----------------------------------------------------------------------
        else if ( strpos( $sql , '?' ) !== false && $prepare != false )
        {
            # ------------------------------------------------------------------
            # 切割SQL語法中的 ? 佔位符
            # ------------------------------------------------------------------
            $sqlArr = explode( '?' , $sql );
            # ------------------------------------------------------------------
            # 防呆
            # ------------------------------------------------------------------
            if ( count( $sqlArr ) != count( $prepare ) )
            {
                return false;
            }
            # ------------------------------------------------------------------
            # 開始處理將參數替換至佔位符號的位置
            # ------------------------------------------------------------------
            foreach( $prepare as $key => $val )
            {
                # --------------------------------------------------------------
                # 如果是使用 ::佔位符 不是 ?佔位符，一定不是 INT
                # --------------------------------------------------------------
                if ( is_numeric( $key ) )
                {
                    $preg_Str .= $sqlArr[$key] . "'" . $val . "'";
                }
                else
                {
                    return false;
                }
            }

            return $preg_Str;
        }

        return false;
    }

    /**
    @brief      select or !! select , 利用這個方式執行
    @param      String      $sql sql語法
    @param      Array       $params_array 替換資料
    @retval     Array | Fasle       ( 資料列 )
    **/
    public function query( $sql , $params_array = null )
    {
        $handle = $this->handle;
        $handle_notice = 'Master';
        # ----------------------------------------------------------------------
        # sql 字串前兩個碼是 !! 則強制用 master db
        # ----------------------------------------------------------------------
        if ( substr( $sql , 0 , 6 ) == 'SELECT' )
        {
            $handle = $this->handle_slave;
            $handle_notice = 'Slave ';
        }
        elseif( substr( $sql , 0 , 2 ) == "!!" )
        {
            $sql = substr( $sql , 2 );
        }
        # ----------------------------------------------------------------------
        # 開始處理資料
        # ----------------------------------------------------------------------
        if ( isset( $params_array ) && is_array( $params_array ) )
        {
            try
            {
                $this->stmt = $handle->prepare( $sql );
                $this->stmt->execute( $params_array );
                # --------------------------------------------------------------
                # 將 "SQL語句、 主機 IP 塞到一個陣列值"
                # --------------------------------------------------------------
                $params_array['Sql_Syntax'] = $sql;
                $params_array['Server_Address'] = $_SERVER["SERVER_ADDR"];
                # --------------------------------------------------------------
                # 除錯記錄 - 取得 SQL 語法
                # --------------------------------------------------------------
                list ( $Pdo_usec , $Pdo_sec ) = explode( ' ' , microtime() );
                Bootstart::$_debug->addMessage( 'queries' , $handle_notice . ': ' . $Pdo_usec , $params_array );
            }
            catch( PDOException $s )
            {
                $this->_PDOException( $s , $sql , $params_array );
                return false;
            }
        }
        else
        {
            try
            {
                # --------------------------------------------------------------
                # 將 "SQL語句、 主機 IP 塞到一個陣列值"
                # --------------------------------------------------------------
                $params_array = array();
                $params_array['Sql_Syntax'] = $sql;
                $params_array['Server_Address'] = $_SERVER["SERVER_ADDR"];
                # --------------------------------------------------------------
                # 除錯記錄 - 取得 SQL 語法
                # --------------------------------------------------------------
                list ( $Pdo_usec , $Pdo_sec ) = explode( ' ' , microtime() );
                Bootstart::$_debug->addMessage( 'queries' , $handle_notice . ': ' . $Pdo_usec , $params_array );
                $this->stmt = $handle->query( $sql );
            }
            catch( PDOException $s )
            {
                $this->_PDOException( $s , $sql , null );
                return false;
            }
        }
        # ----------------------------------------------------------------------
        # 使用setFetchMode方法來設置獲取結果集的返回值的類型
        # ----------------------------------------------------------------------
        $this->stmt->setFetchMode( PDO::FETCH_ASSOC );
        return $this->stmt;
    }

    /**
    @brief      insert, update, delete and DDL , 利用這個方式執行
    @param      String      $sql sql語法
    @param      Array       $params_array 替換資料
    @retval     Int         新增流水號 / 影響筆數
    **/
    public function query_execute( $sql , $params_array = null )
    {
        if ( isset( $params_array ) )
        {
            try
            {
                $this->stmt = $this->handle->prepare( $sql );
                $this->stmt->execute( $params_array );
                # --------------------------------------------------------------
                # 將 "SQL語句、 主機 IP 塞到一個陣列值"
                # --------------------------------------------------------------
                $params_array['Sql_Syntax'] = $sql;
                $params_array['Server_Address'] = $_SERVER["SERVER_ADDR"];
                # --------------------------------------------------------------
                # 除錯記錄 - 取得 SQL 語法
                # --------------------------------------------------------------
                list ( $Pdo_usec , $Pdo_sec ) = explode( ' ' , microtime() );
                Bootstart::$_debug->addMessage( 'queries' , 'Master: ' . $Pdo_usec , $params_array );
                # --------------------------------------------------------------
                # 如果是新增語法的話，就回傳 新增的主鍵ID
                # --------------------------------------------------------------
                if ( preg_match( "/^INSERT|insert/" , $sql ) )
                {
                    $newid = $this->handle->lastInsertId();
                    return ( $newid ? $newid : $this->stmt->rowCount() );
                }
                else
                {
                    return $this->stmt->rowCount();
                }
            }
            catch( PDOException $s )
            {
                $this->_PDOException( $s , $sql , $params_array );
                return false;
            }
        }
        else
        {
            try
            {
                $this->stmt = $this->handle->query( $sql );
                # --------------------------------------------------------------
                # 將 "SQL語句、 主機 IP 塞到一個陣列值"
                # --------------------------------------------------------------
                $params_array = array();
                $params_array['Sql_Syntax'] = $sql;
                $params_array['Server_Address'] = $_SERVER["SERVER_ADDR"];
                # --------------------------------------------------------------
                # 除錯記錄 - 取得 SQL 語法
                # --------------------------------------------------------------
                list ( $Pdo_usec , $Pdo_sec ) = explode( ' ' , microtime() );
                Bootstart::$_debug->addMessage( 'queries' , 'Master: ' . $Pdo_usec , $params_array );
                # --------------------------------------------------------------
                # 如果是新增語法的話，就回傳 新增的主鍵ID
                # --------------------------------------------------------------
                if ( preg_match( "/^INSERT|insert/" , $sql ) )
                {
                    $newid = $this->handle->lastInsertId();
                    return ( $newid ? $newid : $this->stmt->rowCount() );
                }
                else
                {
                    return $this->stmt->rowCount();
                }
            }
            catch( PDOException $s )
            {
                $this->_PDOException( $s , $sql , null );
                return false;
            }
        }
    }

    /**
    @brief      產生分頁
    @param      String      $url 網址
    @param      String      $sql sql 查詢語句，不包括 order by ...
    @param      Array       $prepareArr sql prepare
    @param      String      $sqlOther sql 查詢語句，order by ...
    @param      String      $currentPage 當前頁面
    @param      String      $pageSize 每頁資料顯示數量
    @param      Array       $midPage 偏移數量
    @retval     Array       資料 / 分頁資料
    **/
    public function pagination( $url , $sql , $prepareArr = null , $sqlOther = '' , $currentPage = 1 , $pageSize = 10 , $midPage = 5 , $_notStyle = false)
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $pa = array(); $midPages = '';
        # ----------------------------------------------------------------------
        # 先計算符合此 SQL 語法的總筆數
        # ----------------------------------------------------------------------
        $total = $this->_getCount( $sql , $prepareArr );
        # ----------------------------------------------------------------------
        # 計算分頁數 ( 無條件進位 )
        # ----------------------------------------------------------------------
        $totalPage = ceil( $total / $pageSize );
        # ----------------------------------------------------------------------
        # 如果不為數字 || 且小於1 || 或為空 || 當前頁大於總頁數
        # 就都處理防呆為第一頁
        # ----------------------------------------------------------------------
        if ( ! is_numeric( $currentPage ) || $currentPage < 1 || empty( $currentPage ) || $currentPage > $totalPage )
        {
            $currentPage = 1;
        }
        # ----------------------------------------------------------------------
        # 上一頁
        # ----------------------------------------------------------------------
        $back  = ( $currentPage > 1 ) ? ( $currentPage - 1 ) : 0;
        # ----------------------------------------------------------------------
        # 下一頁
        # ----------------------------------------------------------------------
        $next  = ( $currentPage < $totalPage ) ? ( $currentPage + 1 ) : 0;
        # ----------------------------------------------------------------------
        # 第一頁
        # ----------------------------------------------------------------------
        $first = ( $currentPage > 1 ) ? 1 : 0;
        # ----------------------------------------------------------------------
        # 最末頁
        # ----------------------------------------------------------------------
        $last  = ( $currentPage < $totalPage ) ? $totalPage : 0;
        # ----------------------------------------------------------------------
        # 中間分頁跳頁的部份
        # ----------------------------------------------------------------------
        $num = $currentPage - floor( $midPage / 2 );
        # ----------------------------------------------------------------------
        #...
        # ----------------------------------------------------------------------
        if ( $num > 0 )
        {
            if ( ( $totalPage - $num ) < $midPage )
            {
                $tmp = $totalPage - $midPage;
                $num = ( $tmp < 0 ) ? 1 : ++$tmp;
            }
        }
        else
        {
            $num = 1;
        }
        # ----------------------------------------------------------------------
        # 計算要呈現的跳頁頁數
        # ----------------------------------------------------------------------
        for ( $i = 1; $i <= $midPage; $i++ , $num++ )
        {
            if ( $num > $totalPage ) break;
            $midPages[] = $num;
        }
        # ----------------------------------------------------------------------
        # 產生 SQL 語法用
        # ----------------------------------------------------------------------
        $p2 = ( $currentPage - 1 ) * $pageSize;
        # ----------------------------------------------------------------------
        # 組合 SQL
        # ----------------------------------------------------------------------
        $sql .= " ". $sqlOther . " LIMIT " . $p2 . "," . $pageSize;
        # ----------------------------------------------------------------------
        # 執行查詢動作
        # ----------------------------------------------------------------------
        $rs = $this->query( $sql , $prepareArr )->fetchAll( PDO::FETCH_ASSOC );
        # ----------------------------------------------------------------------
        # 掛載分頁類別
        # ----------------------------------------------------------------------
        Bootstart::$_lib['Core_Loader']->library( 'Pagination' );
        # ----------------------------------------------------------------------
        # 將分頁資料傳入分頁類別中
        # ----------------------------------------------------------------------
        Bootstart::$_lib['Core_Pagination']->__Initialization
        (
            array
            (
                # --------------------------------------------------------------
                # 網址
                # --------------------------------------------------------------
                "url" => $url ,
                # --------------------------------------------------------------
                # 總計
                # --------------------------------------------------------------
                "total" => $total ,
                # --------------------------------------------------------------
                # 單前頁數
                # --------------------------------------------------------------
                "currentPage" => $currentPage ,
                # --------------------------------------------------------------
                # 總頁數
                # --------------------------------------------------------------
                "totalPage" => $totalPage ,
                # --------------------------------------------------------------
                # 上一頁
                # --------------------------------------------------------------
                "back" => $back ,
                # --------------------------------------------------------------
                # 下一頁
                # --------------------------------------------------------------
                "next" => $next ,
                # --------------------------------------------------------------
                # 首頁
                # --------------------------------------------------------------
                "first" => $first ,
                # --------------------------------------------------------------
                # 尾頁
                # --------------------------------------------------------------
                "last" => $last ,
                # --------------------------------------------------------------
                # 中間頁
                # --------------------------------------------------------------
                "midPages" => $midPages ,
                # --------------------------------------------------------------
                # 每頁呈現幾筆
                # --------------------------------------------------------------
                "pagesize" => $pageSize
            )
        );
        # ----------------------------------------------------------------------
        # 判斷是否無需產生分頁樣式
        # ----------------------------------------------------------------------
        if ( $_notStyle )
        {
            $link = array
            (
                "total" => $total ,                                             # 總計
                "currentPage" => $currentPage ,                                 # 單前頁數
                "totalPage" => $totalPage ,                                     # 總頁數
                "back" => $back ,                                               # 上一頁
                "next" => $next ,                                               # 下一頁
                "first" => $first ,                                             # 首頁
                "last" => $last ,                                               # 尾頁
                "midPages" => $midPages ,                                       # 中間頁
                "pagesize" => $pageSize                                         # 每頁呈現幾筆
            );
        }
        else
        {
            $link = Bootstart::$_lib['Core_Pagination']->show( $this->paginstyle );
        }
        # ----------------------------------------------------------------------
        # 回傳組合後的資料
        # ----------------------------------------------------------------------
        return array
        (
            # ------------------------------------------------------------------
            # 用來快速判斷有沒有資料
            # ------------------------------------------------------------------
            'eof'  => ( $total == 0 ) ? false : true ,
            # ------------------------------------------------------------------
            # 資料集
            # ------------------------------------------------------------------
            'f'    => $rs ,
            # ------------------------------------------------------------------
            # 分頁資料陣列
            # ------------------------------------------------------------------
            'link' => $link
        );
    }

    /**
    @brief      將 SQL語法轉為 COUNT( * ) 查詢
    @param      String      $sql sql 查詢語句
    @param      String      $sql prepareArr 替換資料
    @retval     Int         資料數
    **/
    private function _getCount( $sql , $prepareArr )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $m = '';
        # ----------------------------------------------------------------------
        # 如果開頭為 !! 表示強迫使用 Master
        # ----------------------------------------------------------------------
        if ( substr( $sql , 0, 2 ) == '!!' )
        {
            $sql = substr( $sql , 2 );
            $m = '!!';
        }
        # ----------------------------------------------------------------------
        # 組合 SQL 語法
        # ----------------------------------------------------------------------
        $reSql = $m . 'SELECT COUNT(*) AS `COUNT` FROM (' . $sql . ' ) as bsdffe';
        # ----------------------------------------------------------------------
        # 嘗試執行是否有錯誤，有的話交予 Catch 處理錯誤拋出
        # ----------------------------------------------------------------------
        try
        {
            $sth = $this->query( $reSql , $prepareArr );
            return $sth->fetchColumn();
        }
        catch ( PDOException $e )
        {
            echo "Error!: " . $e->getMessage();
            return 0;
        }
    }

    /**
    @brief      PDO 內建功能未實作 InnoDB
    @todo       待了解後實作( 如果很好用的話... )
    **/
    public function beginTransaction()
    {
        $this->handle->beginTransaction();
    }

    /**
    @brief      PDO 內建功能未實作 InnoDB
    @todo       待了解後實作( 如果很好用的話... )
    **/
    public function commit()
    {
        $this->handle->commit();
    }

    /**
    @brief      PDO 內建功能未實作 InnoDB
    @todo       待了解後實作( 如果很好用的話... )
    **/
    public function rollBack()
    {
        $this->handle->rollBack();
    }

    /**
    @brief      when done, we should release resources of database.
    **/
    public function free()
    {
        $this->handle = null;
    }

    /**
    @brief      方便用PDO update 的方法
    @param      String      $table 要異動的資料表名,不要加(``)符號
    @param      Array       $dataArr 要異動的數據欄位
    @param      String      $whereStr 異動時的條件
    @retval     Int | False     影響列數 或是 False
    **/
    public function db_update( $table , $dataArr , $whereStr )
    {
        # ----------------------------------------------------------------------
        # 如果沒有指定使用的資料表 || 沒有指定要更新的數據 就直接回傳錯誤
        # ----------------------------------------------------------------------
        if ( empty( $table ) || ! is_array( $dataArr ) ) return false;
        # ----------------------------------------------------------------------
        # 取出欄位清單array
        # ----------------------------------------------------------------------
        $fieldsList = $this->getFields( $table );
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $tmpArr = $fieldArr = array();
        # ----------------------------------------------------------------------
        # 迴圈組合 Update 語法
        # ----------------------------------------------------------------------
        foreach( $dataArr as $field => $val )
        {
            # ------------------------------------------------------------------
            # 過濾欄位是否存在資料表
            # ------------------------------------------------------------------
            if ( in_array( $field , $fieldsList ) )
            {
                $fieldArr[] = "`{$field}` = :{$field}";
                $tmpArr[":{$field}"] = $val;
            }
        }
        $sql = "UPDATE `{$table}` SET " . implode( "," , $fieldArr );
        # ----------------------------------------------------------------------
        # 判斷是否加上 Where 條件
        # ----------------------------------------------------------------------
        $sql .= ( empty( $whereStr ) ) ? '' : " WHERE {$whereStr}";
        # ----------------------------------------------------------------------
        # 回傳 影響列數 或是 False
        # ----------------------------------------------------------------------
        return $this->query_execute( $sql , $tmpArr );
    }

    /**
    @brief      方便用PDO INSERT 的方法
    @param      String      $table 要異動的資料表名,不要加(``)符號
    @param      Array       $dataArr 要異動的數據欄位
    @param      Array       $updateArr 鍵值重複時要 Update的欄位
    @retval     Int | Bool      新增的流水號 或是 False
    **/
    public function db_insert( $table , $dataArr , $updateArr = null )
    {
        if ( empty( $table ) || ! is_array( $dataArr ) ) return false;
        # ----------------------------------------------------------------------
        # 單純新增時採用排除錯誤的方法
        # ----------------------------------------------------------------------
        $ignore = ( empty( $updateArr ) ) ? 'IGNORE' : '';
        # ----------------------------------------------------------------------
        # 取出欄位清單array
        # ----------------------------------------------------------------------
        $fieldsList = $this->getFields( $table );
        # ----------------------------------------------------------------------
        # 初始化變數
        $tmpArr = $fieldArr = array();
        # ----------------------------------------------------------------------
        # 迴圈組合 SQL 語法
        # ----------------------------------------------------------------------
        foreach( $dataArr as $field => $val )
        {
            # ------------------------------------------------------------------
            # 過濾欄位是否存在資料表
            # ------------------------------------------------------------------
            if ( in_array( $field , $fieldsList ) )
            {
                $fieldArr[] = "`{$field}` = :{$field}";
                $tmpArr[":{$field}"] = $val;
            }
        }
        $sql = "INSERT {$ignore} INTO `{$table}` SET " . implode( "," , $fieldArr );
        # ----------------------------------------------------------------------
        # 鍵值重複時要 Update的欄位
        # ----------------------------------------------------------------------
        if ( is_array( $updateArr ) && count( $updateArr ) )
        {
            # ------------------------------------------------------------------
            # 初始化變數
            # ------------------------------------------------------------------
            $fieldArr = array();
            # ------------------------------------------------------------------
            # 迴圈處理資料
            # ------------------------------------------------------------------
            foreach ( $updateArr as $field => $val )
            {
                # --------------------------------------------------------------
                # 過濾欄位是否存在資料表
                # --------------------------------------------------------------
                if ( in_array( $field , $fieldsList ) )
                {
                    $fieldArr[] = "`{$field}` = :_{$field}";
                    $tmpArr[":_{$field}"] = $val;
                }
            }
            $sql .= " ON DUPLICATE KEY UPDATE " . implode( "," , $fieldArr );
        }
        # ----------------------------------------------------------------------
        # 回傳 新增成功後的流水號ID 或是 FALSE
        # ----------------------------------------------------------------------
        return $this->query_execute( $sql , $tmpArr );
    }

    /**
    @brief      取得資料表的欄位名稱
    @param      String      $table 要查詢的資料表名,不要加(``)符號
    @return     Array
    **/
    public function getFields( $table )
    {
        # ----------------------------------------------------------------------
        # 產生欄位清單array
        # ----------------------------------------------------------------------
        static $fieldsList = array();
        # ----------------------------------------------------------------------
        # 判斷是否存在 Static Array 中
        # ----------------------------------------------------------------------
        if ( ! isset( $fieldsList[$table] ) )
        {
            $my = $this->query( "SHOW FIELDS FROM `{$table}`" );
            while ( $row = $my->fetch() )
            {
                $fieldsList[$table][] = $row['Field'];
            }
            unset($my,$row);
        }
        return $fieldsList[$table];
    }

    /**
    @brief      簡化資料庫讀取的方法
    @param      String      $sql SQL查詢語句僅適用 SELECT
    @param      Array       $paramArr Query時的參數,請參考
    @return     二維Array
    **/
    public function getDB( $sql='' , $paramArr = null )
    {
        if ( ! empty( $sql ) )
        {
            return $this->query( $sql , $paramArr )->fetchAll( PDO::FETCH_ASSOC );
        }
        else
        {
            return false;
        }
    }

    /**
    @brief      回傳資料庫名
    **/
    public function getDatabaseName()
    {
        return $this->dbname;
    }


    /**
    @brief      鎖定MYSQL程序用
    @param      String      $_name Lock別名
    @param      String      $_time 時效
    @retval     Bool        0為鎖定 / 1沒有鎖定
    **/
    public function getLock( $_name = null , $_time = 60 )
    {
        # ----------------------------------------------------------------------
        # 空值直接回傳錯誤
        # ----------------------------------------------------------------------
        if ( $_name === null )
        {
            return false;
        }
        # ----------------------------------------------------------------------
        # 檢查mysql 中是否有鎖定的值存在
        # ----------------------------------------------------------------------
        $rs = $this->query( "!!SELECT IS_FREE_LOCK( '" . $_name . "' ) as " . $_name );
        # ----------------------------------------------------------------------
        # 防呆
        # ----------------------------------------------------------------------
        if ( ! $rs )
        {
            return false;
        }
        # ----------------------------------------------------------------------
        # 取得資料集
        # ----------------------------------------------------------------------
        $row = $rs->fetch();
        # ----------------------------------------------------------------------
        # 如果已經鎖定
        # ----------------------------------------------------------------------
        if ( ! $row[$_name] )
        {
            return false;
        }
        else
        {
            return $this->query( "!!SELECT GET_LOCK( '" . $_name . "' , " . $_time . " )" );
        }
    }

    /**
    @brief      解鎖MYSQL程序用
    @param      String      $_name Lock別名
    @retval     Bool        0為鎖定 / 1沒有鎖定
    **/
    public function releaseLock( $_name = null )
    {
        # ----------------------------------------------------------------------
        # 空值直接回傳錯誤
        # ----------------------------------------------------------------------
        if ( $_name === null )
        {
            return false;
        }
        # ----------------------------------------------------------------------
        # 解除鎖定的值
        # ----------------------------------------------------------------------
        $this->query( "!!SELECT RELEASE_LOCK( '" . $_name . "' ) " );
    }
}