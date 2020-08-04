<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        Party Controller ( 泡泡趴 )
**/

class Party extends Controller
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
        $list = "";
        //判斷手機板
        $userAgent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( $_SERVER['HTTP_USER_AGENT'] ) : 'unknown';
        $isMobileDevice = preg_match("/(iphone|ipod|ipad|android|blackberry|mini|windows\sce|palm)/", $userAgent );
        $w = ( $isMobileDevice ) ? '340px"' : '1024px';

        $video = '
                <div><video width="'.$w.'" height="250" loop="true" autoplay="autoplay"  muted="true" controls>
                    <source type="video/mp4" src="'.DEVIL_APP_PUBLIC_URL.'images/bubble.mp4"></source>
                </video></div>';

        for( $i = 1; $i <= 24; $i ++ )
        {
            $list .= '<div class="col-md-6 col-lg-3 ftco-animate">';
            $list .= '<div class="product">';
            $list .= '<img alt="'.$i.'" class="img-fluid" src="'.DEVIL_APP_PUBLIC_URL.'images/Party/bubble'.$i.'.jpg">';
            $list .= '</div>';
            $list .= '</div>';
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
                # 內容
                # --------------------------------------------------------------
                "VIDEO" => $video ,
                # --------------------------------------------------------------
                # 內容
                # --------------------------------------------------------------
                "LIST" => $list ,
            )
        );
    }
}