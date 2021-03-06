<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );

    /**
    @cond       Config ( 系统配置文件 )        @endcond
    **/

    /**
    @brief      資料庫連線資訊 MASTER
    **/
    $config['system']['masterdb'] = array
    (
        'db_host' => 'localhost' ,
        'db_user' => 'root' ,
        'db_password' => '1234' ,
        'db_database' => 'db_food' ,
        'db_charset' => 'utf8' ,
        'db_port' => '3306' ,
    );

    /**
    @cond       資料庫連線資訊 SLAVE
    **/
    $g_host = array( "localhost" , "localhost" , "localhost" );
    /**
    @endcond    資料庫連線資訊 SLAVE
    **/

    /**
    @brief      資料庫連線資訊 SLAVE
    **/
    $config['system']['slavedb'] = array
    (
        # ----------------------------------------------------------------------
        # 隨機獲取一個陣列
        # ----------------------------------------------------------------------
        'db_host' => $g_host[array_rand( $g_host )] ,
        'db_user' => 'root' ,
        'db_password' => '1234' ,
        'db_database' => 'db_food' ,
        'db_charset' => 'utf8' ,
        'db_port' => '3306' ,
    );

    /**
    @brief      自定義類的擴充名稱 ( 自定義類庫的文件前綴 )
    **/
    $config['system']['lib'] = array
    (
        'prefix' => 'My'
    );

    /**
    @brief      自定義輔助函式的擴充名稱 ( 自定義類輔助函式的文件前綴 )
    **/
    $config['system']['helper'] = array
    (
        'prefix' => 'My'
    );

    /**
    @brief      Cache設定
    **/
    $config['system']['cache'] = array
    (
        # ----------------------------------------------------------------------
        # Cache路徑，相對於根目錄
        # ----------------------------------------------------------------------
        'cache_dir' => 'cache' ,
        # ----------------------------------------------------------------------
        # Cache文件名稱前缀
        # ----------------------------------------------------------------------
        'cache_prefix' => 'cache_' ,
        # ----------------------------------------------------------------------
        # Cache暫存時間
        # ----------------------------------------------------------------------
        'cache_time' => 1800 ,
        # ----------------------------------------------------------------------
        # mode 1 為serialize , model 2為保存為可執行文件
        # ----------------------------------------------------------------------
        'cache_mode' => 2 ,
    );

    /**
    @brief      全線禁止登入轉跳網址設定
    **/
    $config['system']['disabled'] = array
    (
        # ----------------------------------------------------------------------
        # 奇摩   中國
        # ----------------------------------------------------------------------
        'http://cn.yahoo.com/' ,
        # ----------------------------------------------------------------------
        # 百度   中國
        # ----------------------------------------------------------------------
        'http://www.baidu.com/' ,
        # ----------------------------------------------------------------------
        # 新浪網 中國
        # ----------------------------------------------------------------------
        'http://www.sina.com.cn/' ,
    );
