<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        Home Controller ( 首頁 )
**/

class Home extends Controller
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
        # 初始化
        # ----------------------------------------------------------------------
        $myfile = $banner = "";
        $dataArr = array();
        # ----------------------------------------------------------------------
        # 檔案路徑
        # ----------------------------------------------------------------------
        $fn = dirname( dirname( dirname(__FILE__) ) ) . DIRECTORY_SEPARATOR . "Player_Area"  . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "carousel.txt";
        # ----------------------------------------------------------------------
        # 防呆
        # ----------------------------------------------------------------------
        if ( file_exists( $fn ) )
        {
            # ------------------------------------------------------------------
            # 取出檔案內容
            # ------------------------------------------------------------------
            $myfile = file_get_contents( $fn , 'r' );
            # ------------------------------------------------------------------
            # 轉成陣列
            # ------------------------------------------------------------------
            $dataArr = json_decode( $myfile , true )[ "TT" ];
        }
        # ----------------------------------------------------------------------
        # 轉成陣列
        # ----------------------------------------------------------------------
        if( $dataArr )
        {
            foreach( $dataArr as $key => $val )
            {
                $banner .= '<div class="slider-item" style="background-image: url('.DEVIL_APP_PUBLIC_URL . 'images/banner/'.$val[ "id" ].'.png'.');">';
                $banner .= '<div class="overlay"></div>';
                $banner .= '<div class="container">';
                $banner .= '<div class="row slider-text justify-content-center align-items-center" data-scrollax-parent="true">';
                $banner .= '<div class="col-md-12 ftco-animate text-center">';
                $banner .= '<h1 class="mb-2">We serve Fresh Vegestables &amp; Fruits</h1>';
                $banner .= '<h2 class="subheading mb-4"></h2>';
                $banner .= '<p><a class="btn btn-primary" href='.DEVIL_APP_Url.'LunchBox'.'>購物去</a></p>';
                $banner .= '</div>';
                $banner .= '</div>';
                $banner .= '</div>';
                $banner .= '</div>';
            }
        }

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
                # --------------------------------------------------------------
                # 輪播圖
                # --------------------------------------------------------------
                "BANNER" => $banner ,
            )
        );
    }
}