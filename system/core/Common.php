<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );

    /**
    @brief      取出 SESSION 資料
    @param      String      $index
    @retval     String | False
    @remarks    此範例結果：3。
    @code{.unparsed}
    Core_LoadSession( 'id' );
    @endcode
    **/
    function Core_LoadSession( $index )
    {
        if ( isset( $_SESSION['Login_user'][$index] ) )
        {
            return $_SESSION['Login_user'][$index];
        }
        else
        {
            return false;
        }
    }

    /**
    @brief      取出核心內的 Bootstart static Array
    @param      String      $static_name
    @param      String      $index
    @retval     Array
    @remarks    此範例返回：設定於 hip.php 檔案的超級陣列。
    @code{.unparsed}
    Core_LoadBootStatic( '_hip' );
    @endcode
    **/
    function Core_LoadBootStatic( $static_name , $index = null )
    {
        if ( isset( Bootstart::$$static_name ) )
        {
            # ------------------------------------------------------------------
            # 取出 Bootstart 的靜態屬性
            # ------------------------------------------------------------------
            $staticArr = Bootstart::$$static_name;
            # ------------------------------------------------------------------
            # 如果沒有特別指定要取的值，就回傳全部的內容
            # ------------------------------------------------------------------
            if ( $index === null )
            {
                return $staticArr;
            }
            else
            {
                return $staticArr[$index];
            }
        }
        else
        {
            return array();
        }
    }

    /**
    @brief      判斷是否已經登入
    @note       先用 cookie, 減少對資料庫的存取,預防封包造假,把時間作簡單的加密
    **/
    function Core_IsLoginCookie()
    {
        $_EntryStr = Bootstart::$_lib['Core_Cookie']->get( '_c_chk' );
        # ------------------------------------------------------------------
        # 判斷登入
        # ------------------------------------------------------------------
        if ( ! isset( $_EntryStr ) || $_EntryStr === false )
        {
            return false;
        }
        # ------------------------------------------------------------------
        # 解密
        # ------------------------------------------------------------------
        $_EntryStrArr = explode( '||' , Bootstart::$_lib['Core_EncryptDecryt']->decrypt( $_EntryStr ) );
        # ------------------------------------------------------------------
        # 長度不正確
        # ------------------------------------------------------------------
        if ( count( $_EntryStrArr ) != 3 )
        {
            return false;
        }

        return true;
    }

    /**
    @brief      檢查PHP.ini設置參數有沒有存在
    @param      String      $varName PHP模組名稱
    @remarks    此範例結果：返回名為 Test 的cookie值。
    @code{.unparsed}
    Core_ini_cheak( 'mcrypt.modes_dir' );
    @endcode
    **/
    function Core_Cheak_PhpIni( $varName )
    {
        # ----------------------------------------------------------------------
        # 取得PHP.ini的所有設定
        # ----------------------------------------------------------------------
        $php_ini_allArr = ini_get_all();
        # ----------------------------------------------------------------------
        # 如果有取到指定的值 $varName
        # ----------------------------------------------------------------------
        if ( ! isset( $php_ini_allArr[$varName] ) )
        {
            show_error( 'The : ' . $varName . ' is not setting.' );
        }
    }

    /**
    @brief      計算當前記憶體用量
    @remarks    此範例返回：1.7MB - 注意此結果僅為參考用。
    @code{.unparsed}
    Core_Memory();
    @endcode
    **/
    function Core_Memory()
    {
        $mem_usage = memory_get_usage();
        if ( $mem_usage < 1024 )
        {
            return $mem_usage . " B";
        }
        elseif ( $mem_usage < 1048576 )
        {
            return round( $mem_usage / 1024,2 ) . " KB";
        }
        else
        {
            return round( $mem_usage / 1048576 , 2 ) ." MB";
        }
    }

    /**
    @brief      設定時區台灣台北 ( PHP5設定時區, 在PHP4無法使用, 所以另外處理 )
    @remarks    此設定已於核心入口處執行，專案無需再執行一次。
    @code{.unparsed}
    Core_TimeZoneSet();
    @endcode
    **/
    function Core_TimeZoneSet()
    {
        if ( function_exists( 'date_default_timezone_set' ) )
        {
            # --------------------------------------------------------------
            # PHP5設定時區, 在PHP4無法使用
            # --------------------------------------------------------------
            date_default_timezone_set( 'Asia/Taipei' );
        }
        else
        {
            # --------------------------------------------------------------
            # PHP4設定時區的用法
            # --------------------------------------------------------------
            putenv( "TZ=Asia/Taipei" );
        }
    }

    /**
    @brief      判斷是不是POST
    @retval     Bool
    @remarks    判斷當下行為確定為 post 才接續處理。
    @code{.unparsed}
    if ( Core_IsPost() )
    {
        Yes...
    }
    else
    {
        No...
    }
    @endcode
    **/
    function Core_IsPost()
    {
        return ( $_SERVER["REQUEST_METHOD"] == "POST" );
    }

    /**
    @brief      重新導向網址
    @param      String      $urls 指定的網址
    @param      Bool        $type 指定專案網址 || 指定外部網址 default false
    @param      Bool        $statusText Header文字
    @remarks    此設定已於核心入口處執行，專案無需再執行一次。
    @code{.unparsed}
    Core_Redirect( 'http://www.yahoo.com.tw' );
    @endcode
    **/
    function Core_Redirect( $urls , $type = false , $statusText = '' )
    {
        # ------------------------------------------------------------------
        # 如果是AJAX呼叫、然後又是執行登出的功能，就不做轉跳
        # ------------------------------------------------------------------
        if ( Core_IsAjax() && $statusText != '' )
        {
            header( "HTTP/1.1 999 {$statusText}" );
            exit;
        }
        else
        {
            if ( isset( $urls ) )
            {
                if ( $type )
                {
                    header( "Location:" . $urls );
                    exit;
                }
                else
                {
                    header( "Location:" . DEVIL_APP_Url . '' . $urls );
                    exit;
                }
            }
            else
            {
                header( "Location:" . DEVIL_APP_Url . "" );
                exit;
            }
        }
    }

    /**
    @brief      顯示帳號過濾 - 不直接顯示超級帳號
    @param      String      $acc 帳號
    @param      String      $_replace 要取代的文字，預設為：SYSTEM_ADMIN
    @remarks    此範例返回：WaterMan。
    @code{.unparsed}
    Core_CheckAccShow( 'BadMan' , 'WaterMan' );
    @endcode
    **/
    function Core_CheckAccShow( $acc = '' , $_replace = 'SYSTEM_ADMIN' )
    {
        if ( $acc == DEVIL_APP_SUPERACCOUNT )
        {
            # --------------------------------------------------------------
            # 表示系統管理員.顯示名稱日後可討論修改
            # --------------------------------------------------------------
            return $_replace;
        }
        else
        {
            return $acc;
        }
    }

    /**
    @brief      單純輸出文字用
    @param      String      $string 輸出的文字
    @remarks    此範例返回：你好!。
    @code{.unparsed}
    Core_Export( '你好!' );
    @endcode
    **/
    function Core_Export( $string )
    {
        header( "Content-Type:text/html; charset=utf-8" );
        die( $string );
    }

    /**
    @brief      DeBug
    @param      String | Array      $string 要顯示的文字，也可以是陣列。
    @remarks    此範例返回：
    @code
    <pre>
    array
    (
        A ,
        B
    )
    </pre>
    @endcode
    @code{.unparsed}
    Core_PreDie( array( A , B ) );
    @endcode
    **/
    function Core_PreDie( $string )
    {
        echo header( "Content-Type:text/html; charset=utf-8" );
        echo '<pre>';
        print_r( $string );
        echo '</pre>';
        die;
    }

    /**
    @brief      錯誤訊息
    @param      String      $message 錯誤提示
    @param      Int         $status_code Http Code
    @param      String      $heading 頁面的標題
    **/
    function show_error( $message , $status_code = 500 , $heading = 'An Error Was Encountered' )
    {
        echo Bootstart::$_lib['Core_Error']->show_error( $heading , $message , 'error_general' , $status_code );
        exit;
    }

    /**
    @brief      404錯誤訊息
    @param      String      $page
    @param      String      $log_error
    **/
    function show_404( $page = '' , $log_error = true )
    {
        Bootstart::$_lib['Core_Error']->show_404( $page , $log_error );
        exit;
    }

    /**
    @brief      回傳當前的 "程式" 是由哪一支檔案呼叫的
    @return     String
    **/
    function Core_GetCallerInfo()
    {
        Core_preDie( debug_backtrace() );
    }

    /**
    @brief      檢查是否為ajax
    **/
    function Core_IsAjax()
    {
        return ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' );
    }

    /**
    @brief      產生伺服器的標頭，強制資料被下載到 客戶端 的電腦
    @param      String      $filename 檔案名稱，包含副檔名。
    @param      Mixed       $data 檔案內容
    **/
    function Core_ForceDownload( $filename = '' , $data = '' )
    {
        if ( $filename == '' || $data == '' )
        {
            return false;
        }
        # ------------------------------------------------------------------
        # 嘗試確定如果文件名中包含的文件擴展名,用來設置MIME類型
        # ------------------------------------------------------------------
        if ( false === strpos( $filename , '.' ) )
        {
            return false;
        }
        # ------------------------------------------------------------------
        # 取得檔案副檔名
        # ------------------------------------------------------------------
        $x = explode( '.' , $filename );
        $extension = end( $x );
        # ------------------------------------------------------------------
        # 讀取 MIMES 設定檔
        # ------------------------------------------------------------------
        if ( defined('DEVIL_APP_ENVIRONMENT') && is_file( DEVIL_APP_APPLOCATION . 'appconfig' . DIRECTORY_SEPARATOR . DEVIL_APP_ENVIRONMENT . DIRECTORY_SEPARATOR . 'mimes.php' ) )
        {
            include( DEVIL_APP_APPLOCATION . 'appconfig' . DIRECTORY_SEPARATOR . DEVIL_APP_ENVIRONMENT . DIRECTORY_SEPARATOR . 'mimes.php' );
        }
        elseif ( is_file( DEVIL_APP_APPLOCATION . 'appconfig' . DIRECTORY_SEPARATOR . 'mimes.php' ) )
        {
            include( DEVIL_APP_APPLOCATION . 'appconfig' . DIRECTORY_SEPARATOR .'mimes.php' );
        }
        # ------------------------------------------------------------------
        # 如果找不到當前副檔名，就預設此下載 MIMES 標頭
        # ------------------------------------------------------------------
        if ( ! isset( $mimes[$extension] ) )
        {
            $mime = 'application/octet-stream';
        }
        else
        {
            $mime = ( is_array( $mimes[$extension] ) ) ? $mimes[$extension][0] : $mimes[$extension];
        }
        # ------------------------------------------------------------------
        # 產生標頭
        # ------------------------------------------------------------------
        if ( strpos( $_SERVER['HTTP_USER_AGENT'] , "MSIE" ) !== false )
        {
            header( 'Content-Type: "' . $mime .'"' );
            header( 'Content-Disposition: attachment; filename="'.$filename.'"' );
            header( 'Expires: 0' );
            header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
            header( 'Content-Transfer-Encoding: binary' );
            header( 'Pragma: public' );
            header( 'Content-Length: ' . strlen( $data ) );
        }
        else
        {
            header( 'Content-Type: "' . $mime . '"' );
            header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
            header( 'Content-Transfer-Encoding: binary' );
            header( 'Expires: 0' );
            header( 'Pragma: no-cache' );
            header( 'Content-Length: ' . strlen( $data ) );
        }
        exit( $data );
    }

    /**
    @brief       錯誤訊息頁面
    @param      String      $filename 檔案名稱，包含副檔名。
    @param      Mixed       $data 檔案內容
    **/
    function show_errormsg(  $_message , $_href = 'javascript:history.back(-1);'  )
    {
        # ----------------------------------------------------------------------
        # 初始化
        # ----------------------------------------------------------------------
        $messageTag = '';
        # ----------------------------------------------------------------------
        # 判斷是否為陣列
        # ----------------------------------------------------------------------
        if ( is_array( $_message ) )
        {
            $messageTag .= '<ul>';
            foreach ( $_message as $k => $v )
            {
                $messageTag .= '<li>'.$v.'</li>';
            }
            $messageTag .= '</ul>';
        }
        else
        {
            $messageTag .= $_message;
        }
        # ----------------------------------------------------------------------
        # 回傳
        # ----------------------------------------------------------------------
        $dataArr = array
        (
            # ------------------------------------------------------------------
            # 導航條
            # ------------------------------------------------------------------
            'HREF' => $_href ,
            'L_BACK' => "回上一頁" ,
            'L_ERRORTIPS' => "錯誤提示" ,
            'ERRORTIPS' => $messageTag
        );
        $show =  Bootstart::$_lib['My_HtmlView']->Extension_HtmlView
        (
            "Core_Errornoty_Page" ,
            "Core_Errornoty_Page.html" ,
            $dataArr
        );
        exit($show);
    }

    /**
    @brief      圖片uri
    @param      String      $filename 檔案名稱，包含副檔名。
    @param      Mixed       $data 檔案內容
    **/
function create_data_uri( $source_file ) {
  $encoded_string = base64_encode(file_get_contents($source_file));
  return $encoded_string;
}
