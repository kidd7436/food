<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        Miyue  Controller ( 彌月禮盒 )
**/

class Miyue  extends Controller
{
    private $dataArr =  array();
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
        # 檔案路徑
        # ----------------------------------------------------------------------
        $fn = dirname( dirname( dirname(__FILE__) ) ) . DIRECTORY_SEPARATOR . "Player_Area"  . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "product.txt";
        # ----------------------------------------------------------------------
        # 防呆
        # ----------------------------------------------------------------------
        if( file_exists( $fn ) )
        {
            # ------------------------------------------------------------------
            # 取出檔案內容
            # ------------------------------------------------------------------
            $myfile = file_get_contents( $fn , 'r' );
            # ------------------------------------------------------------------
            # 轉成陣列
            # ------------------------------------------------------------------
            $this->dataArr = json_decode( $myfile , true )[ 2 ];
        }
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
        # ----------------------------------------------------------------------
        # 防呆
        # ----------------------------------------------------------------------
        if( $this->dataArr )
        {
            foreach( $this->dataArr as $key => $val )
            {
                $list .= '<div class="col-md-6 col-lg-3 ftco-animate">';
                $list .= '<div class="product">';
                $list .= '<a class="img-prod" href="'.DEVIL_APP_Url.__CLASS__.'/Detail?name='.$val[ "title" ].'"><img alt="'.$val[ "title" ].'" class="img-fluid" src="'.DEVIL_APP_PUBLIC_URL.'images/product/'.$val[ "id" ].'.jpg">';
                $list .= '<div class="overlay"></div></a>';
                $list .= '<div class="text py-3 pb-4 px-3 text-center">';
                $list .= '<h3><a href="#">'.$val[ "title" ].'</a></h3>';
                $list .= '<div class="d-flex">';
                $list .= '<div class="pricing">';
                $list .= '<p class="price">';
                $list .= ( $val[ "money" ] < $val[ "discount" ] ) ? '<span class="mr-2 price-dc">$120.00</span>' : "";
                $list .= '<span class="price-sale">$'.$val[ "money" ].'</span>';
                $list .= '</p>';
                $list .= '</div>';
                $list .= '</div>';
                $list .= '</div>';
                $list .= '</div>';
                $list .= '</div>';
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
                # 內容
                # --------------------------------------------------------------
                "LIST" => $list ,
            )
        );
    }

    /**
    @brief      詳細
    **/
    public function Detail( )
    {
        # ----------------------------------------------------------------------
        # 防呆 取出產品名稱
        # ----------------------------------------------------------------------
        if( ! isset( $_GET[ "name" ] ) )
        {
            Core_Redirect( __CLASS__ );
        }
        $name = $_GET[ "name" ];
        # ----------------------------------------------------------------------
        # 防呆，沒有資料就導回原頁面
        # ----------------------------------------------------------------------
        if( ! isset( $this->dataArr[ $name ] ) )
        {
            Core_Redirect( __CLASS__ );
        }
        # ----------------------------------------------------------------------
        # 初始化
        # ----------------------------------------------------------------------
        $detail = $list  = "";
        # ----------------------------------------------------------------------
        # 資料另外用變數儲存
        # ----------------------------------------------------------------------
        $detail = $this->dataArr[ $name ];
        # ----------------------------------------------------------------------
        # 內容
        # ----------------------------------------------------------------------
        $list .= '<div class="col-lg-6 mb-5 ftco-animate">';
        $list .= '<a href="#" class="image-popup"><img src="'.DEVIL_APP_PUBLIC_URL.'images/product/'.$detail[ "id" ].'.jpg" class="img-fluid" alt="'.$detail[ "title" ].'"></a>';
        $list .= '</div>';
        $list .= '<div class="col-lg-6 product-details pl-md-5 ftco-animate">';
        $list .= '<h3>'.$detail[ "title" ].'</h3>';
        $list .= '<p class="price"><span id="money">$'.( ( $detail[ "money" ] > $detail[ "discount" ] ) ? $detail[ "discount" ] : $detail[ "money" ] ).'</span></p>';
        $list .= '<p>'.$detail[ "content" ].'</p>';
        $list .= '<div class="row mt-4">';
        $list .= '<div class="col-md-6">';
        $list .= '<div class="form-group d-flex"></div>';
        $list .= '</div>';
        $list .= '<div class="w-100"></div>';
        $list .= '<div class="input-group col-md-6 d-flex mb-3">';
        $list .= '<span class="input-group-btn mr-2">';
        $list .= '<button type="button" class="quantity-left-minus btn"  data-type="minus" data-field="">';
        $list .= '<i class="ion-ios-remove"></i>';
        $list .= '</button>';
        $list .= '</span>';
        $list .= '<input type="text" id="quantity" name="quantity" class="form-control input-number" value="1" min="1" max="100">';
        $list .= '<span class="input-group-btn ml-2">';
        $list .= '<button type="button" class="quantity-right-plus btn" data-type="plus" data-field="">';
        $list .= '<i class="ion-ios-add"></i>';
        $list .= '</button>';
        $list .= '</span>';
        $list .= '</div>';
        $list .= '<div class="w-100"></div>';
        $list .= '<div class="col-md-12">';
        //$list .= '<p style="color: #000;">600 kg available</p>';
        $list .= '</div>';
        $list .= '</div>';
        $list .= '<input type="hidden" name="title" value="'.$detail[ "title" ].'">';
        $list .= '<input type="hidden" name="id" value="'.$detail[ "id" ].'">';
        $list .= '<p><a href="javascript:" onclick="BuyCar()" class="btn btn-black py-3 px-5">放入購物車</a></p>';
        $list .= '</div>';
        # ----------------------------------------------------------------------
        # 輸出內容
        # ----------------------------------------------------------------------
        Bootstart::$_lib[ 'My_HtmlView' ]->Extension_HtmlView
        (
            "Detail_Page" ,
            "Detail.html" ,
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
                "LIST" => $list ,
                # --------------------------------------------------------------
                # 大類名稱
                # --------------------------------------------------------------
                "NAME" => '彌月禮盒' ,
            )
        );
    }
}