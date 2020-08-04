<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        Qna Controller ( Q & A )
**/

class Qna extends Controller
{
    /**
    @brief      建構子
    **/
    public function __construct()
    {
        # ----------------------------------------------------------------------
        # 呼叫父類別建構子
        # ----------------------------------------------------------------------
        parent::__construct();
        # ----------------------------------------------------------------------
        # 先將核心自動載入的類別實例化至當前類別中
        # ----------------------------------------------------------------------
        parent::__initialization();
    }

    /**
    @brief      登入後 看到的第一頁 ( 首頁 )
    **/
    public function index(  )
    {
        # ----------------------------------------------------------------------
        # 輸出內容
        # ----------------------------------------------------------------------
        Bootstart::$_lib[ 'My_HtmlView' ]->Extension_HtmlView
        (
            __CLASS__."_Page" ,
            __CLASS__.".html" ,
            array
            (
                # --------------------------------------------------------------
                # 當前網址
                # --------------------------------------------------------------
                "URL" => DEVIL_APP_Url ,
                # --------------------------------------------------------------
                # JS、CSS、圖片等的存取網址
                # --------------------------------------------------------------
                "PUBLIC_URL" => DEVIL_APP_PUBLIC_URL ,
                # --------------------------------------------------------------
                # 網頁 TITLE
                # --------------------------------------------------------------
                "TITLE" => DEVIL_APP_PROJECT_NAME ,
            )
        );
    }
}