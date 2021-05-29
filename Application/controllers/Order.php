<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        Order Controller ( 訂單管理控制器 )
**/

class Order extends Controller
{
    /**
    @brief      存放查询用阵列( 允许的索引值 )
    **/
    private $_standard = array ( 'acc', 'no', 'status', 'ps', 'dts', 'dte', 'dts_ok', 'dte_ok', 'am1', 'am2', 'updateaccount' );

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
        if ( Bootstart::$_lib['My_Power']->isPower( 7 ) === FALSE )
        {
            Core_Redirect( 'Op' );
        }
        # ----------------------------------------------------------------------
        # 手動載入需要的類別
        # ----------------------------------------------------------------------
        Bootstart::$_lib[ 'Core_Loader' ]->library( array( 'Files', 'EncryptDecryt' ) );
        Bootstart::$_lib[ 'Core_Loader' ]->model( array( 'Users', 'Order' ) );
        # ----------------------------------------------------------------------
        # 重新建構類別
        # ----------------------------------------------------------------------
        $this->__initialization();
    }

    /**
    @brief      index處理導向
    **/
    public function index( $_type = 'list', $pages = 1, $val = '' )
    {
        $pages = intval( $pages );
        # ----------------------------------------------------------------------
        # 全部轉小寫，方便判斷
        # ----------------------------------------------------------------------
        switch( strtolower( $_type ) )
        {
            # ------------------------------------------------------------------
            # 預設列表畫面
            # ------------------------------------------------------------------
            case 'list':
            case 'searchlist':
                self::_datalist( $pages, $val );
                break;
            # ------------------------------------------------------------------
            # 修改處理程序
            # ------------------------------------------------------------------
            case 'post':
                    self::_post( );
                    break;
            # ------------------------------------------------------------------
            # 查詢
            # ------------------------------------------------------------------
            case 'searchpost':
                self::_encodeSrcPost( );
                break;
        }
    }

    /**
    @brief      輪播圖管理( 首頁 )
    **/
    public function _datalist( $_pages, $_search )
    {
        # ----------------------------------------------------------------------
        # 過濾導向過來的值
        # ----------------------------------------------------------------------
        $srcStr = Bootstart::$_lib[ 'Core_EncryptDecryt' ]->decrypt( $_search );
        # ----------------------------------------------------------------------
        # 切空字串為陣列
        # ----------------------------------------------------------------------
        $releaseExplodeArray = explode( "," , $srcStr );
        # ----------------------------------------------------------------------
        # 查詢資料有問題，正常不會進來( 防呆 )
        # ----------------------------------------------------------------------
        if ( count( $releaseExplodeArray ) == count( $this->_standard ) )
        {
            $releaseExplodeArray = array_combine( $this->_standard , $releaseExplodeArray );
        }
        # ----------------------------------------------------------------------
        # 帳號
        # ----------------------------------------------------------------------
        $_acc = array_key_exists( 'acc', $releaseExplodeArray ) ? $releaseExplodeArray[ 'acc' ] : '';
        # ----------------------------------------------------------------------
        # 訂單號
        # ----------------------------------------------------------------------
        $_no = array_key_exists( 'no', $releaseExplodeArray ) ? $releaseExplodeArray[ 'no' ] : '';
        # ----------------------------------------------------------------------
        # 狀態
        # ----------------------------------------------------------------------
        $_status = intval( array_key_exists( 'status', $releaseExplodeArray ) ? $releaseExplodeArray[ 'status' ] : -1 );
        # ----------------------------------------------------------------------
        # 筆數
        # ----------------------------------------------------------------------
        $_ps = array_key_exists( 'ps', $releaseExplodeArray ) ? intval( $releaseExplodeArray[ 'ps' ] ) : 20;
        # ----------------------------------------------------------------------
        # 申請開始日期
        # ----------------------------------------------------------------------
        $_dts = array_key_exists( 'dts', $releaseExplodeArray ) ? $releaseExplodeArray[ 'dts' ] : date( "Y-m-d 00:00:00" );
        # ----------------------------------------------------------------------
        # 申請結束日期
        # ----------------------------------------------------------------------
        $_dte = array_key_exists( 'dte', $releaseExplodeArray ) ? $releaseExplodeArray[ 'dte' ] : date( "Y-m-d 23:59:59" );
        # ----------------------------------------------------------------------
        # 申確認開始日期
        # ----------------------------------------------------------------------
        $_dts_ok = array_key_exists( 'dts_ok' , $releaseExplodeArray ) ? $releaseExplodeArray[ 'dts_ok' ] : '';
        # ----------------------------------------------------------------------
        # 確認結束日期
        # ----------------------------------------------------------------------
        $_dte_ok = array_key_exists( 'dte_ok' , $releaseExplodeArray ) ? $releaseExplodeArray[ 'dte_ok' ] : '';
        # ----------------------------------------------------------------------
        # 入款单金额 - 起始
        # ----------------------------------------------------------------------
        $_am1 = array_key_exists( 'am1', $releaseExplodeArray ) ? intval( $releaseExplodeArray[ 'am1' ] ) : 0;
        # ----------------------------------------------------------------------
        # 入款单金额 - 结束
        # ----------------------------------------------------------------------
        $_am2 = array_key_exists( 'am2', $releaseExplodeArray ) ? intval( $releaseExplodeArray[ 'am2' ] ) : 0;
        # ----------------------------------------------------------------------
        # 操作者
        # ----------------------------------------------------------------------
        $_updateacc = array_key_exists( 'updateaccount', $releaseExplodeArray ) ? $releaseExplodeArray[ 'updateaccount' ] : '';
        # ----------------------------------------------------------------------
        # 判断帐号 :: 帐号放前面,因为帐号没有后面就不用查了
        # ----------------------------------------------------------------------
        if ( $_acc != '' )
        {
            # ------------------------------------------------------------------
            # 判斷帳號是否存在
            # ------------------------------------------------------------------
            $accArr = Bootstart::$_mod[ 'Users_Model' ]->getUsersByAcc( $_acc );
            # ------------------------------------------------------------------
            # 不存在就中斷
            # ------------------------------------------------------------------
            if ( ! $accArr )
            {
                show_errormsg( "查無此帳號！！" );
            }
            $srcArr[] = " user_id = " . $accArr['id'];
        }
        # ----------------------------------------------------------------------
        # 判斷是否有訂單號
        # ----------------------------------------------------------------------
        if ( $_no )
        {
            $srcArr[] = " `order_id` = {$_no} ";
            $srcStrArr[] = "<span class='label label-info'>订单号：{$_no}</span>";
        }
        # ----------------------------------------------------------------------
        # 判斷狀態
        # ----------------------------------------------------------------------
        if ( $_status > -1 )
        {
            $status = intval( $_status );
            $srcArr[] = " `status` = {$status} ";
            $statusArr = array( 0 => '待處理', 1 => '已匯款', 2 => '已完成', 3 => '取消' );
            $srcStrArr[] = "<span class='label label-info'>狀態：" . ( isset( $statusArr[ $status ] ) ? $statusArr[ $status ] : '-' ) . "</span>";
        }
        # ----------------------------------------------------------------------
        # 是否要使用預設日期
        # ----------------------------------------------------------------------
        $default_dt_flag = 0;
        # ----------------------------------------------------------------------
        # 判斷申請日期區間
        # ----------------------------------------------------------------------
        if ( $_dts || $_dte )
        {
            $dts = $_dts;
            $dte = $_dte;
            # ------------------------------------------------------------------
            # 日期格式正確
            # ------------------------------------------------------------------
            if ( $dts && $dte )
            {
                $srcArr[] = ' `createtime` BETWEEN ' . strtotime( $dts ) . ' AND ' . strtotime( $dte ) ;
                $srcStrArr[] = "<span class='label label-info'>申请：{$dts} ～ {$dte}</span>";
            }
            elseif ( $dts )
            {
                $srcArr[] = ' `createtime` > ' . strtotime( $dts ) ;
                $srcStrArr[] = "<span class='label label-info'>申请：{$dts}～</span>";
            }
            elseif ( $dte )
            {
                $srcArr[] = ' `createtime` < ' . strtotime( $dte  ) ;
                $srcStrArr[] = "<span class='label label-info'>申请：～{$dte}</span>";
            }
        }
        else
        {
            $default_dt_flag = 1;
        }
        # ----------------------------------------------------------------------
        # 判斷確認日期區間
        # ----------------------------------------------------------------------
        if ( $_dts_ok || $_dte_ok )
        {
            $dts_ok = $_dts_ok;
            $dte_ok = $_dte_ok;
            # ------------------------------------------------------------------
            # 日期格式正確
            # ------------------------------------------------------------------
            if ( $dts_ok && $dte_ok )
            {
                $srcArr[] = " `withdrawtime` BETWEEN " . strtotime( $dts_ok ) . " AND " . strtotime( $dte_ok );
                $srcStrArr[] = "<span class='label label-info'>确认：{$dts_ok} ～ {$dte_ok}</span>";
            }
            elseif( $dts_ok )
            {
                $dts = $_dts_ok;
                $srcArr[] = " `withdrawtime` > " . strtotime( $dts_ok ) ;
                $srcStrArr[] = "<span class='label label-info'>确认：{$dts_ok}～</span>";
                $dte = '';
            }
            elseif( $dte_ok )
            {
                $dte = $_dte_ok;
                $srcArr[] = " `withdrawtime` < " . strtotime( $dte_ok ) ;
                $srcStrArr[] = "<span class='label label-info'>确认：～{$dte_ok}</span>";
                $dts = '';
            }
            # ------------------------------------------------------------------
            # 有新查询
            # ------------------------------------------------------------------
            $default_dt_flag = 0;
        }
        # ----------------------------------------------------------------------
        # 預設查詢本日 + 昨日
        # ----------------------------------------------------------------------
        if ( $default_dt_flag )
        {
            $showTime = strtotime( date('Y-m-d', time() ) ) - 86400;
            $srcArr[] = " `createtime` > {$showTime}" ;
            $srcStrArr[] = "<span class='label label-info'>申请：" . date('Y-m-d H:i' , $showTime ) . "～</span>";
        }
        # ----------------------------------------------------------------------
        # 判斷是否有金額
        # ----------------------------------------------------------------------
        if ( $_am1 && $_am2 )
        {
            $srcArr[] = " amount1 BETWEEN {$_am1} AND {$_am2} ";
            $srcStrArr[] = "<span class='label label-info'>金额：{$_am1} ~ {$_am2}</span>";
        }
        elseif ( $_am1 )
        {
            $srcArr[] = " amount1 > {$_am1} ";
            $srcStrArr[] = "<span class='label label-info'>金额：超过{$_am1}</span>";
        }
        elseif ( $_am2 )
        {
            $srcArr[] = " amount1 < {$_am2} ";
            $srcStrArr[] = "<span class='label label-info'>金额：低于{$_am2}</span>";
        }
        # ----------------------------------------------------------------------
        # 操作者
        # ----------------------------------------------------------------------
        if( $_updateacc )
        {
            $srcArr[] = " `account` LIKE '%" . $_updateacc . "%'";
            $srcStrArr[] = "<span class='label label-info'>操作者：{$_updateacc}</span>";
        }
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $list = "";
        # ----------------------------------------------------------------------
        # 取出所有餐盒資料
        # ----------------------------------------------------------------------
        $dataArr =  Bootstart::$_mod[ 'Order_Model' ]->getOrderdata( $_pages, $srcArr, $_ps );
        # ----------------------------------------------------------------------
        # 防呆 有資料才做
        # ----------------------------------------------------------------------
        if( $dataArr[ "f" ] )
        {
            # ------------------------------------------------------------------
            # 產生 Table
            # ------------------------------------------------------------------
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_table( '' , 'table table-striped table-bordered table-hover table-checkable align-center' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_TSection( 'thead' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_row();
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( '訂單號' , 'th' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( '訂購者' , 'th' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( '地址' , 'th' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( '電話' , 'th' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( '訂購日期' , 'th' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( '訂購內容' , 'th' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( '金額' , 'th' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( '狀態' , 'th' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( '備註' , 'th' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( '功能' , 'th' );
            Bootstart::$_lib[ 'Core_HtmlTable' ]->add_TSection( 'tbody' );
            # ------------------------------------------------------------------
            # 狀態對照
            # ------------------------------------------------------------------
            $status = array( 0 => '待處理', 1 => '已匯款', 2 => '已完成', 3 => '取消' );
            # ------------------------------------------------------------------
            # 產生tr
            # ------------------------------------------------------------------
            foreach( $dataArr[ "f" ] as $key => $val )
            {
                # --------------------------------------------------------------
                # 初始化
                # --------------------------------------------------------------
                $edit = "";
                # --------------------------------------------------------------
                # 初始化
                # --------------------------------------------------------------
                //$edit = '<a class="btn btn-xs bs-tooltip" href="'.DEVIL_APP_Url.__CLASS__."/index/edit/".$val['id'].'" title="修改"><i class="icon-edit"></i></a>&nbsp;&nbsp;';
                # --------------------------------------------------------------
                # 防呆
                # --------------------------------------------------------------
                switch( $val[ 'status' ] )
                {
                    case 0:
                        $edit .= '<button class="btn btn-xs bs-tooltip" title="已付款" onClick="check( 1, '.$val['id'].')"><i class="icon-money"></i></button>&nbsp;&nbsp;';
                    break;
                    case 1:
                        $edit .= '<button class="btn btn-xs bs-tooltip" title="已完成" onClick="check( 2, '.$val['id'].')"><i class="icon-ok"></i></button>&nbsp;&nbsp;';
                    break;
                    default:
                        $edit .= "";
                    break;
                }
                # --------------------------------------------------------------
                # 產生功能按鈕
                # --------------------------------------------------------------
                $edit .= '<button class="btn btn-xs bs-tooltip btn-danger" title="取消" onClick="check( 3, '.$val['id'].')"><i class="icon-remove"></i></button>';
                # --------------------------------------------------------------
                # tr
                # --------------------------------------------------------------
                Bootstart::$_lib[ 'Core_HtmlTable' ]->add_row();
                Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( $val[ 'order_id' ] );
                Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( $val[ 'name' ] );
                Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( $val[ 'address' ] );
                Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( $val[ 'phone' ] );
                Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( date( "Y-m-d H:i:s", $val[ 'createtime' ] ) );
                Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( $val[ 'content' ] );
                Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( $val[ 'amount' ] );
                Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( $status[ $val[ 'status' ] ] );
                Bootstart::$_lib[ 'Core_HtmlTable' ]->add_cell( $val[ 'note' ] );
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
                # 查詢條件
                # --------------------------------------------------------------
                "SRCSTR" => count( $srcStrArr ) > 0 ? implode( " " , $srcStrArr ) : '' ,
                # --------------------------------------------------------------
                # 資料
                # --------------------------------------------------------------
                "LIST" => $list,
                # --------------------------------------------------------------
                # 申請日期區間 開始
                # --------------------------------------------------------------
                "DTS" => date( "Y-m-d 00:00:00" ) ,
                # --------------------------------------------------------------
                # 申請日期區間 結束
                # --------------------------------------------------------------
                "DTE" => date( "Y-m-d 23:59:59" ) ,
                # --------------------------------------------------------------
                # 查詢
                # --------------------------------------------------------------
                "SRC_POST_URL" => DEVIL_APP_Url . __CLASS__ . '/index/searchpost' ,
                # --------------------------------------------------------------
                # 修改
                # --------------------------------------------------------------
                "POST" => DEVIL_APP_Url . __CLASS__ . '/index/post' ,
            )
        );
    }

    /**
    @brief      訂單管理( 狀態修改程序 )
    **/
    private function _post( )
    {
        if ( Core_IsPost() && $_POST != '' )
        {
            # ------------------------------------------------------------------
            # 初始化變數
            # ------------------------------------------------------------------
            $id = $status = '';
            # ------------------------------------------------------------------
            # 檢查是否有帶id進來
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
                    echo json_encode( array( "message" => 'ID類型錯誤！！' ) ); die();
                }
            }
            else
            {
                echo json_encode( array( "message" => 'ID重大錯誤！！' ) ); die();
            }
            # ------------------------------------------------------------------
            # 檢查是否有帶type進來
            # ------------------------------------------------------------------
            if( isset( $_POST[ 'status' ] ) )
            {
                # --------------------------------------------------------------
                # 過濾『STAUTS』
                # --------------------------------------------------------------
                $status = Bootstart::$_lib[ 'Core_Input' ]->post( 'status' , 'int' );
                # --------------------------------------------------------------
                # 錯誤輸出
                # --------------------------------------------------------------
                if ( ! $status  )
                {
                    echo json_encode( array( "message" => 'STAUTS類型錯誤！！' ) ); die();
                }
            }
            else
            {
                echo json_encode( array( "message" => 'STAUTS重大錯誤！！' ) ); die();
            }
            # ------------------------------------------------------------------
            # 組成資訊
            # ------------------------------------------------------------------
            $dataArr = array();
            $dataArr[ 'status' ] = $status;
            $dataArr[ "updateid" ] = Core_LoadSession( 'id' );
            $dataArr[ "updatedt" ] = date('Y-m-d H:i:s');
            $dataArr[ "updateip" ] = sprintf( "%u" , ip2long ( Bootstart::$_lib['Core_UserAgent']->ip ) );
            # ------------------------------------------------------------------
            # 寫檔
            # ------------------------------------------------------------------
            if( Bootstart::$_lib[ 'Core_Pdo_Driver' ]->db_update( 'orderdata' , $dataArr , "id = {$id}" ) === FALSE )
            {
                echo json_encode( array( "message" => '修改失敗！！' ) ); die();
            }
            else
            {
                # --------------------------------------------------------------
                # 確定匯款成功，才通知
                # --------------------------------------------------------------
                if( $status == 1 )
                {
                    $this->msgSend($id);
                }
                echo json_encode( array( "message" => '狀態更新成功！！' ) ); die();
            }
        }
        Core_Redirect( __CLASS__ );
    }

    /**
    @brief      处理查询资料,转成GET模式
    **/
    private function _encodeSrcPost()
    {
        if ( Core_IsPost() && $_POST != '' )
        {
            # ------------------------------------------------------------------
            # 初始化
            # ------------------------------------------------------------------
            $srcArr = array();
            # ------------------------------------------------------------------
            # 這裡的頭序要和searchlist配合
            # ------------------------------------------------------------------
            $srcArr = array_fill_keys( $this->_standard , '' );
            # ------------------------------------------------------------------
            # 帳號
            # ------------------------------------------------------------------
            $srcArr['acc'] = Bootstart::$_lib[ 'Core_Input' ]->post( 'acc' , 'string' );
            # ------------------------------------------------------------------
            # 訂單號
            # ------------------------------------------------------------------
            $srcArr['no'] = Bootstart::$_lib[ 'Core_Input' ]->post( 'no' , 'string' );
            # ------------------------------------------------------------------
            # 狀態
            # ------------------------------------------------------------------
            $srcArr['status'] = $_POST[ "status" ];
            # ------------------------------------------------------------------
            # 筆數
            # ------------------------------------------------------------------
            $srcArr['ps'] = Bootstart::$_lib[ 'Core_Input' ]->post( 'ps' , 'int' );
            # ------------------------------------------------------------------
            # 申請開始日期
            # ------------------------------------------------------------------
            $srcArr['dts'] = $_POST[ "dts" ];
            # ------------------------------------------------------------------
            # 申請結束日期
            # ------------------------------------------------------------------
            $srcArr['dte'] = $_POST[ "dte" ];
            # ------------------------------------------------------------------
            # 確認開始日期
            # ------------------------------------------------------------------
            $srcArr['dts_ok'] = $_POST[ "dts_ok" ];
            # ------------------------------------------------------------------
            # 確認結束日期
            # ------------------------------------------------------------------
            $srcArr['dte_ok'] = $_POST[ "dte_ok" ];
            # ------------------------------------------------------------------
            # 金额起
            # ------------------------------------------------------------------
            $srcArr['am1'] = Bootstart::$_lib[ 'Core_Input' ]->post( 'am1' , 'int' );
            # ------------------------------------------------------------------
            # 金额迄
            # ------------------------------------------------------------------
            $srcArr['am2'] = Bootstart::$_lib[ 'Core_Input' ]->post( 'am2' , 'int' );
            # ------------------------------------------------------------------
            # 操作者
            # ------------------------------------------------------------------
            $srcArr['updateaccount'] = Bootstart::$_lib[ 'Core_Input' ]->post( 'updateaccount' , 'string' );
            # ------------------------------------------------------------------
            # 组合加密
            # ------------------------------------------------------------------
            $srcStr = Bootstart::$_lib[ 'Core_EncryptDecryt' ]->encrypt( implode( "," , $srcArr ) );
            # ------------------------------------------------------------------
            # 判断有没有指定的分页
            # ------------------------------------------------------------------
            $_pages = min( 2147483647 , max( 1 , Bootstart::$_lib[ 'Core_Input' ]->post( 'pages' , 'int' )) );
            # ------------------------------------------------------------------
            # 转跳至指定查询页面
            # ------------------------------------------------------------------
            Core_Redirect( implode( '/' , array( __CLASS__ , 'index' , 'searchlist', $_pages, $srcStr ) ) );
        }
        else
        {
            Core_Redirect( __CLASS__ );
        }
    }

    /**
    @brief      msgSend
    **/
    private function msgSend( $_id )
    {
        # ----------------------------------------------------------------------
        # 如果是正式模式才會發送簡訊
        # ----------------------------------------------------------------------
        if( DEVIL_APP_ENVIRONMENT == 'production' )
        {
            # ------------------------------------------------------------------
            # 取出指定的訂單資料
            # ------------------------------------------------------------------
            $dataArr =  Bootstart::$_mod[ 'Order_Model' ]->getOrderById( $_id );
            # ------------------------------------------------------------------
            # 防呆
            # ------------------------------------------------------------------
            if( $dataArr )
            {
                # --------------------------------------------------------------
                # 初始化
                # --------------------------------------------------------------
                $msg = '';
                # --------------------------------------------------------------
                # 內容
                # --------------------------------------------------------------
                $msg .= "訂單號：" . $dataArr[ "order_id" ] .","
                     . "訂購內容：" . $dataArr[ "content" ] .","
                     . "訂單已成立,"
                     . "感謝您的訂購！！祝您順心！！";
                # --------------------------------------------------------------
                # 產生API連結
                # --------------------------------------------------------------
                $buf = "https://smsb2c.mitake.com.tw/b2c/mtk/SmSend?CharsetURL=UTF-8&username=0915919871&password=5764&dstaddr=".$dataArr[ "phone" ].""
                    . "&DestName=".$dataArr[ "name" ]."&dlvtime=&vldtime=1000&smbody=". $msg
                    . "&response=http://192.168.1.200/smreply.asp";
                # --------------------------------------------------------------
                # 呼叫API
                # --------------------------------------------------------------
                $strTemp = curl_google( $buf , false );
                // $curl = curl_init();
                // // url
                // $url = 'https://smsb2c.mitake.com.tw/b2c/mtk/SmSend?';
                // $url .= 'CharsetURL=UTF-8';
                // // parameters
                // $data = 'username=0915919871';
                // $data .= '&password=5764';
                // $data .= '&dstaddr='.$phone.'';
                // $data .= '&smbody='.$msg.'';
                // // 設定curl網址
                // curl_setopt($curl, CURLOPT_URL, $url);
                // // 設定Header
                // curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-formurlencoded"));
                // curl_setopt($curl, CURLOPT_POST, 1);
                // curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                // curl_setopt($curl, CURLOPT_HEADER,0);
                // // 執行
                // $output = curl_exec($curl);
                // curl_close($curl);
                // echo $output;
            }
        }
    }

    // /**
    // @brief      msgSend
    // **/
    // private function msgUrl( $_id )
    // {
    //     # ----------------------------------------------------------------------
    //     # 如果是正式模式才會發送簡訊
    //     # ----------------------------------------------------------------------
    //     if( DEVIL_APP_ENVIRONMENT == 'production' )
    //     {
    //         # ------------------------------------------------------------------
    //         # 取出指定的訂單資料
    //         # ------------------------------------------------------------------
    //         $dataArr =  Bootstart::$_mod[ 'Order_Model' ]->getOrderById( $_id );
    //         # ------------------------------------------------------------------
    //         # 防呆
    //         # ------------------------------------------------------------------
    //         if( $dataArr )
    //         {
    //             # --------------------------------------------------------------
    //             # 初始化
    //             # --------------------------------------------------------------
    //             $phone = $msg = '';
    //             # --------------------------------------------------------------
    //             # 電話號碼
    //             # --------------------------------------------------------------
    //             $phone = $dataArr[ "phone" ];
    //             # --------------------------------------------------------------
    //             # 內容
    //             # --------------------------------------------------------------
    //             $msg .= "訂單號：" . $dataArr[ "order_id" ] ."<br>"
    //                  . "訂購內容：" . $dataArr[ "content" ] ."<br>"
    //                  . "訂單已成立<br>"
    //                  . "感謝您的訂購！！祝您順心！！";
    //             # --------------------------------------------------------------
    //             # 產生API連結
    //             # --------------------------------------------------------------
    //             //https://sms.mitake.com.tw/
    //             // $buf = "http://smexpress.mitake.com.tw/SmSendGet.asp?username=0915919871&password=0926&dstaddr=".$phone.""
    //             //     . "&encoding=UTF8&DestName=service&dlvtime=&vldtime=1000&smbody=". rawurlencode( $msg )
    //             //     . "&response=http://192.168.1.200/smreply.asp;
    //             $buf = "https://sms.mitake.com.tw/b2c/mtk/SmSend?CharsetURL=UTF-8&username=0915919871&password=0926&dstaddr=".$phone.""
    //                  . "&smbody=". rawurlencode( $msg );

    //             core_predie($buf);
    //                 # --------------------------------------------------------------
    //             # 呼叫API
    //             # --------------------------------------------------------------
    //             $strTemp = curl_google( $buf , false );
    //         }

    //     }
    // }
}