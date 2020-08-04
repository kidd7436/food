<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        Carousel Controller ( 輪播圖管理控制器 )
**/

class Carousel extends Controller
{
    private $kinds = "TT";
    private $imgurl = '';
    /**
    @brief      建構子
    **/
    public function __construct()
    {
        # ----------------------------------------------------------------------
        # 呼叫 "父類別" 的 __backstageInit Method
        # ----------------------------------------------------------------------
        $this->__backstageInit();
        # ----------------------------------------------------------------------
        # 在此處統一判斷有無 "帳號管理" , 沒權限導向到首頁
        # ----------------------------------------------------------------------
        if ( Bootstart::$_lib['My_Power']->isPower( 9 ) === FALSE )
        {
            Core_Redirect( 'Op' );
        }
        # ----------------------------------------------------------------------
        # 手動載入需要的類別
        # ----------------------------------------------------------------------
        Bootstart::$_lib[ 'Core_Loader' ]->model( array( 'Carousel' ) );
        # ----------------------------------------------------------------------
        # 後台圖片存檔路徑
        # ----------------------------------------------------------------------
        $this->imgurl = DEVIL_SYSTEM_PATH . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR , array( "Player_Area" , "public" , "images" ,"banner" ) ) . DIRECTORY_SEPARATOR;
        # ----------------------------------------------------------------------
        # 重新建構類別
        # ----------------------------------------------------------------------
        $this->__initialization();
    }

    /**
    @brief      index處理導向
    **/
    public function index( $_type = 'list', $_id = 0 )
    {
        # ----------------------------------------------------------------------
        # 過濾參數
        # ----------------------------------------------------------------------
        $id = intval( $_id );
        # ----------------------------------------------------------------------
        # 全部轉小寫，方便判斷
        # ----------------------------------------------------------------------
        switch( strtolower( $_type ) )
        {
            # ------------------------------------------------------------------
            # 預設列表畫面
            # ------------------------------------------------------------------
            case 'list':
                self::_datalist( );
                break;
            # ------------------------------------------------------------------
            # 新增
            # ------------------------------------------------------------------
            case 'add':
            # ------------------------------------------------------------------
            # 修改
            # ------------------------------------------------------------------
            case 'edit':
                self::_add( $id );
                break;
            # ------------------------------------------------------------------
            # 修改處理程序
            # ------------------------------------------------------------------
            case 'post':
                    self::_post( );
                    break;
            # ------------------------------------------------------------------
            # 上傳圖片
            # ------------------------------------------------------------------
            case 'upd_logo':
                self::_upd_logo( $id );
                break;
            # ------------------------------------------------------------------
            # 圖片上傳程序
            # ------------------------------------------------------------------
            case 'upd':
                self::_upd( );
                break;
            # ------------------------------------------------------------------
            # 刪除
            # ------------------------------------------------------------------
            case 'del':
                self::_del( $id );
                break;
        }
    }

    /**
    @brief      輪播圖管理( 首頁 )
    **/
    public function _datalist( )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $list = "";
        # ----------------------------------------------------------------------
        # 取出所有餐盒資料
        # ----------------------------------------------------------------------
        $dataArr =  Bootstart::$_mod[ 'Carousel_Model' ]->getCarousel( $this->kinds );
        # ----------------------------------------------------------------------
        # 防呆 有資料才做
        # ----------------------------------------------------------------------
        if( $dataArr )
        {
            # ------------------------------------------------------------------
            # 產生 Table
            # ------------------------------------------------------------------
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_table( '' , 'table table-striped table-bordered table-hover table-checkable align-center' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_TSection( 'thead' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_row();
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( '標題' , 'th' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( '連結網址' , 'th' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( '狀態' , 'th' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( '功能' , 'th' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_TSection( 'tbody' );
            # ------------------------------------------------------------------
            # tag狀態
            # ------------------------------------------------------------------
            $enabled = array(
                '0' => '<span class="label label-danger">停用</span>' ,
                '1' => '<span class="label label-success">啟用</span>'
            );
            # ------------------------------------------------------------------
            # 產生tr
            # ------------------------------------------------------------------
            foreach( $dataArr as $key => $val )
            {
                # --------------------------------------------------------------
                # 初始化
                # --------------------------------------------------------------
                $imgPath  = $imgType = $imgData = $imgBase64 = $img = "";
                # --------------------------------------------------------------
                # 取得圖檔路徑
                # --------------------------------------------------------------
                $imgPath = $this->imgurl . $val[ 'id' ].".png";
                $imgType = pathinfo( $imgPath , PATHINFO_EXTENSION );
                $img  = "";
                if( is_file( $imgPath ) )
                {
                    $imgData = file_get_contents( $imgPath );
                    $imgBase64 = 'data:image/' . $imgType . ';base64,' . base64_encode( $imgData );
                    $img = '<img src="'. $imgBase64 .'">';
                }
                # --------------------------------------------------------------
                # 產生功能按鈕
                # --------------------------------------------------------------
                $edit = '<a class="btn btn-xs bs-tooltip" rel="popover" data-img="'.$imgBase64.'"><i class="icon-eye-open"></i></a>&nbsp;&nbsp;'
                      . '<a class="btn btn-xs bs-tooltip" href="'.DEVIL_APP_Url.__CLASS__."/index/edit/".$val['id'].'" title="修改"><i class="icon-edit"></i></a>&nbsp;&nbsp;'
                      . '<a class="btn btn-xs bs-tooltip" href="'.DEVIL_APP_Url.__CLASS__."/index/upd_logo/".$val['id'].'" title="上傳圖片"><i class="icon-cloud-upload"></i></a>&nbsp;&nbsp;'
                      . '<button class="btn btn-xs bs-tooltip btn-danger" title="Delete" onClick="del('.$val['id'].')"><i class="icon-trash"></i></button>';
                # --------------------------------------------------------------
                # tr
                # --------------------------------------------------------------
                Bootstart::$_lib[ 'Core_HtmlTable' ]->add_row();
                Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( $val[ 'title' ] );
                Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( $val[ 'link' ] );
                Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( $enabled[ $val[ 'enabled' ] ] );
                Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( $edit );
            }
            # ------------------------------------------------------------------
            # 產生所有設定的表格資料
            # ------------------------------------------------------------------
            $list = Bootstart::$_lib[ 'Core_HtmlTable' ]->generate();
        }
        else
        {
            $list = '<div class="alert alert-info alert-dismissible">
                        <h5><i class="icon fas fa-info"></i> 無任何資料！！</h5>
                    </div>';
        }
        # ----------------------------------------------------------------------
        # 輸出內容
        # ----------------------------------------------------------------------
        $this->My_HtmlView->Extension_HtmlView
        (
            __CLASS__.".Page" ,
            __CLASS__.".html" ,
            array
            (
                # --------------------------------------------------------------
                # 站名
                # --------------------------------------------------------------
                "TITLE" => DEVIL_APP_PROJECT_NAME,
                # --------------------------------------------------------------
                # JS、CSS、圖片等的存取網址
                # --------------------------------------------------------------
                "PUBLIC_URL" => DEVIL_APP_PUBLIC_URL,
                # --------------------------------------------------------------
                # 當前網址
                # --------------------------------------------------------------
                "URL" => DEVIL_APP_Url,
                # --------------------------------------------------------------
                # 資料
                # --------------------------------------------------------------
                "LIST" => $list,
                # --------------------------------------------------------------
                # 新增商品連結
                # --------------------------------------------------------------
                "ADD" => DEVIL_APP_Url . __CLASS__ . '/index/add/',
                # --------------------------------------------------------------
                # 刪除商品連結
                # --------------------------------------------------------------
                "DEL" => DEVIL_APP_Url . __CLASS__ . '/index/del/',
            )
        );
    }

    /**
    @brief      輪播圖管理( 新增、修改頁面 )
    **/
    private function _add( $_id )
    {
        # ----------------------------------------------------------------------
        # 初始化
        # ----------------------------------------------------------------------
        $htmlArr[ "title" ] = "";
        $htmlArr[ "link" ] = "";
        $htmlArr[ "enabled0" ] = "";
        $htmlArr[ "enabled1" ] = "checked='checked'";
        $htmlArr[ "HIDDEN_ID" ] = "";
        $htmlArr[ "button" ] = "新增";
        $htmlArr[ "sort" ] = "";
        $weekDay = "";
        $peddledt = array();
        # ----------------------------------------------------------------------
        # 這裡處理修改
        # ----------------------------------------------------------------------
        if( $_id )
        {
            # ------------------------------------------------------------------
            # 初始化
            # ------------------------------------------------------------------
            $dataArr = array();
            # ------------------------------------------------------------------
            # 取出指定的餐盒資料
            # ------------------------------------------------------------------
            $dataArr = Bootstart::$_mod[ 'Carousel_Model' ]->getCarouselById( $id );
            # ------------------------------------------------------------------
            # 防呆
            # ------------------------------------------------------------------
            if( ! $dataArr )
            {
                show_errormsg( "錯誤的編號！！" );
            }
            # ------------------------------------------------------------------
            # 商品資料
            # ------------------------------------------------------------------
            $htmlArr = $dataArr;
            # ------------------------------------------------------------------
            # 處理『狀態』
            # ------------------------------------------------------------------
            $htmlArr[ "enabled0" ] = ( ( $dataArr[ "enabled" ] == 0 ) ? 'checked="checked"' : "" );
            $htmlArr[ "enabled1" ] = ( ( $dataArr[ "enabled" ] == 1 ) ? 'checked="checked"' : '' );
            # ------------------------------------------------------------------
            # ID
            # ------------------------------------------------------------------
            $htmlArr[ "HIDDEN_ID" ] = '<input type="hidden" name="id" value="'.$dataArr[ "id" ].'" />';
            # ------------------------------------------------------------------
            # 按鈕文字
            # ------------------------------------------------------------------
            $htmlArr[ "button" ] = "修改";
        }
        # ----------------------------------------------------------------------
        # HTML
        # ----------------------------------------------------------------------
        $htmlArr[ "TITLE" ] = DEVIL_APP_PROJECT_NAME;
        $htmlArr[ "PUBLIC_URL" ] = DEVIL_APP_PUBLIC_URL;
        $htmlArr[ "URL" ] = DEVIL_APP_Url;
        $htmlArr[ "POST_URL" ] = DEVIL_APP_Url . __CLASS__ . '/index/post/';
        $htmlArr[ "UPD_URL" ] = DEVIL_APP_Url . __CLASS__ . '/index/upd/';
        # ----------------------------------------------------------------------
        # 輸出內容
        # ----------------------------------------------------------------------
        $this->My_HtmlView->Extension_HtmlView
        (
            __CLASS__."_Add_Page" ,
            __CLASS__."_Add.html" ,
            $htmlArr
        );
    }

    /**
    @brief      輪播圖管理( 新增、俢改程序 )
    **/
    private function _post( )
    {
        if ( Core_IsPost() && $_POST != '' )
        {
            # ------------------------------------------------------------------
            # 初始化變數
            # ------------------------------------------------------------------
            $id = $title = $link = '';
            $sort = 1;
            # ------------------------------------------------------------------
            # 判斷是修改 還是 新增
            # ------------------------------------------------------------------
            if( isset( $_POST[ 'id' ] ) )
            {
                # --------------------------------------------------------------
                # 過濾『ID』
                # --------------------------------------------------------------
                $id = Bootstart::$_lib[ 'Core_Input' ]->post( 'id' , 'int' );
                # --------------------------------------------------------------
                # 錯誤輸出
                # --------------------------------------------------------------
                if ( ! $id  )
                {
                    show_errormsg( '重大錯誤！！' );
                }
            }
            # ------------------------------------------------------------------
            # 過濾『TITLE』
            # ------------------------------------------------------------------
            if( $_POST[ 'title' ] == "" )
            {
                show_errormsg( '請輸入標題！！' );
            }
            else
            {
                $title = $_POST[ "title" ];
            }
            # ------------------------------------------------------------------
            # 非必填 檢查有送資料在檢查格式
            # ------------------------------------------------------------------
            if( !empty( $_POST[ 'link' ] ) )
            {
                # --------------------------------------------------------------
                # 過濾『DISCOUNT』
                # --------------------------------------------------------------
                $link = $_POST[ 'link' ];
            }
            # ------------------------------------------------------------------
            # 過濾『排序』
            # ------------------------------------------------------------------
            $sort = Bootstart::$_lib[ 'Core_Input' ]->post( 'sort' , 'int' );
            # ------------------------------------------------------------------
            # 過濾『狀態』
            # ------------------------------------------------------------------
            $enabled = Bootstart::$_lib[ 'Core_Input' ]->post( 'enabled' , 'int' );
            $enabled = ( ( $enabled == false ) ? 0 :  intval( $enabled ) );
            # ------------------------------------------------------------------
            # 組成資訊
            # ------------------------------------------------------------------
            $dataArr = array();
            $dataArr[ 'title' ] = $title;
            $dataArr[ 'kinds' ] = $this->kinds;
            $dataArr[ 'link' ] = $link;
            $dataArr[ 'enabled' ] = $enabled;
            $dataArr[ 'sort' ] = $sort;
            $dataArr[ "updateid" ] = Core_LoadSession( 'id' );
            $dataArr[ "updatedt" ] = date('Y-m-d H:i:s');
            $dataArr[ "updateip" ] = sprintf( "%u" , ip2long ( Bootstart::$_lib['Core_UserAgent']->ip ) );
            # ------------------------------------------------------------------
            # 如果有帶 ID 代表是修改
            # ------------------------------------------------------------------
            if( $id )
            {
                if( Bootstart::$_lib[ 'Core_Pdo_Driver' ]->db_update( 'carousel' , $dataArr , "id = {$id}" ) === FALSE )
                {
                    show_errormsg( '修改失敗，請重新輸入！！' );
                }
            }
            # ------------------------------------------------------------------
            # 新增這裡處理
            # ------------------------------------------------------------------
            else
            {
                if( Bootstart::$_lib[ 'Core_Pdo_Driver' ]->db_insert( 'carousel' , $dataArr ) === FALSE )
                {
                    show_errormsg( '新增失敗，請重新輸入！！' );
                }
            }
            # ------------------------------------------------------------------
            # 寫檔
            # ------------------------------------------------------------------
            Bootstart::$_mod[ 'Carousel_Model' ]->writeTxt( $this->kinds );
            # ------------------------------------------------------------------
            # 返回
            # ------------------------------------------------------------------
            Core_Redirect( __CLASS__ );
        }
        Core_Redirect( __CLASS__ );
    }

    /**
    @brief      輪播圖管理( 上傳圖片 )
    **/
    private function _upd_logo( $_id )
    {
        # ----------------------------------------------------------------------
        # 過濾
        # ----------------------------------------------------------------------
        $id = $_id;
        # ----------------------------------------------------------------------
        # 初始化
        # ----------------------------------------------------------------------
        $dataArr = array();
        # ----------------------------------------------------------------------
        # 取出指定的餐盒資料
        # ----------------------------------------------------------------------
        $dataArr = Bootstart::$_mod[ 'Carousel_Model' ]->getCarouselById( $id );
        # ----------------------------------------------------------------------
        # 防呆
        # ----------------------------------------------------------------------
        if( !$dataArr )
        {
            show_errormsg( '查無此內容！！' );
        }
        # ----------------------------------------------------------------------
        # 商品資料
        # ----------------------------------------------------------------------
        $htmlArr = $dataArr;
        # ----------------------------------------------------------------------
        # ID
        # ----------------------------------------------------------------------
        $htmlArr[ "HIDDEN_ID" ] = '<input type="hidden" name="id" value="'.$dataArr[ "id" ].'" />';
        # ----------------------------------------------------------------------
        # 其它內容
        # ----------------------------------------------------------------------
        $htmlArr[ "TITLE" ] = DEVIL_APP_PROJECT_NAME;
        $htmlArr[ "PUBLIC_URL" ] = DEVIL_APP_PUBLIC_URL;
        $htmlArr[ "URL" ] = DEVIL_APP_Url;
        $htmlArr[ "UPD_URL" ] = DEVIL_APP_Url . __CLASS__ . '/index/upd/';
        # ----------------------------------------------------------------------
        # 輸出內容
        # ----------------------------------------------------------------------
        $this->My_HtmlView->Extension_HtmlView
        (
            __CLASS__."_Upd_Page" ,
            __CLASS__."_Upd.html" ,
            $htmlArr
        );
    }

    /**
    @brief      輪播圖管理( 圖片上傳程序 )
    **/
    private function _upd( )
    {
        if ( Core_IsPost() && $_POST != '' )
        {
            # ------------------------------------------------------------------
            # 檢查圖片類型
            # ------------------------------------------------------------------
            if( $_FILES[ "pictures" ][ "type" ] != 'image/png' )
            {
                show_errormsg( '圖片格式錯誤，請重新上傳！！' );
            }
            # ------------------------------------------------------------------
            # 檢查圖片大小
            # ------------------------------------------------------------------
            if( $_FILES[ "pictures" ][ "size" ] > '524288' )
            {
                show_errormsg( '圖片過大，請重新上傳！！' );
            }
            # ------------------------------------------------------------------
            # 過濾『ID』
            # ------------------------------------------------------------------
            $id = intval( $_POST[ "id" ] );
            # ------------------------------------------------------------------
            # 上傳
            # ------------------------------------------------------------------
            if( ! move_uploaded_file( $_FILES[ "pictures" ][ "tmp_name" ], $this->imgurl.$id.'.png' ) )
            {
                show_errormsg( '圖片上傳失敗，請重新上傳！！' );
            }
            Core_Redirect( __CLASS__ );
        }
        Core_Redirect( __CLASS__ );
    }

    /**
    @brief      輪播圖管理( 刪除 )
    **/
    private function _del( $_id )
    {
        # ----------------------------------------------------------------------
        # 過濾『ID』
        # ----------------------------------------------------------------------
        $id = intval( $_id );
        # ----------------------------------------------------------------------
        # 防呆
        # ----------------------------------------------------------------------
        if( $id )
        {
            # ------------------------------------------------------------------
            # 執行更新
            # ------------------------------------------------------------------
            if ( Bootstart::$_lib[ 'Core_Pdo_Driver' ]->query( "DELETE FROM `carousel` WHERE `id` = '{$id}'" ) === FALSE )
            {
                show_errormsg( '刪除失败！' );
            }
            # ------------------------------------------------------------------
            # 刪除圖片
            # ------------------------------------------------------------------
            if( file_exists( $this->imgurl.$id.'.png' ) )
            {
                unlink( $this->imgurl.$id.'.png' );
            }
            # ------------------------------------------------------------------
            # 寫檔
            # ------------------------------------------------------------------
            Bootstart::$_mod[ 'Carousel_Model' ]->writeTxt( $this->kinds );
        }
        Core_Redirect( __CLASS__ );
    }
}