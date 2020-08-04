<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        資料採集類，用來取得指定網站的資料。
  @version      1.0.0
  @date         2015-02-27
  @since        1.0.0 -> 新增此新類別。
  @see          http://xyz.cinc.biz/2014/01/php-curl-curlopt-timeout-ms.html
**/

class Curl
{
    /**
    @brief      http_data
    **/
    protected $http_data = array();

    /**
    @brief      agent
    **/
    protected $agent;

    /**
    @brief      cookies
    **/
    protected $cookies;

    /**
    @brief      referer
    **/
    protected $referer;

    /**
    @brief      ip
    **/
    protected $ip;

    /**
    @brief      header
    **/
    protected $header = array();

    /**
    @brief      _option
    **/
    protected $_option = array();

    /**
    @brief      _post_data
    **/
    protected $_post_data = array();

    /**
    @brief      多列隊任務進程數，0表示不限制。 類型：Int
    **/
    protected $multi_exec_num = 100;

    /**
    @brief      設置agent
    @param      String      $agent
    @retval     Object
    **/
    public function set_agent( $agent )
    {
        $this->agent = $agent;
        return $this;
    }

    /**
    @brief      設置cookie
    @param      String      $cookies
    @retval     Object
    **/
    public function set_cookies( $cookies )
    {
        $this->cookies = $cookies;
        return $this;
    }

    /**
    @brief      設置$referer
    @param      String      $referer
    @retval     Object
    **/
    public function set_referer( $referer )
    {
        $this->referer = $referer;
        return $this;
    }

    /**
    @brief      設置IP
    @param      String      $ip
    @retval     Object
    **/
    public function set_ip( $ip )
    {
        $this->ip = $ip;
        return $this;
    }

    /**
    @brief      獲取重新包裝後的 Curl
    @retval     Array
    **/
    public function get_result_data()
    {
        return $this->http_data;
    }

    /**
    @brief      設置curl參數
    @param      String      $key
    @param      Value       $value
    @retval     Object
    **/
    public function set_option( $key , $value )
    {
        if ( $key === CURLOPT_HTTPHEADER )
        {
            $this->header = array_merge( $this->header , $value );
        }
        else
        {
            $this->_option[$key] = $value;
        }

        return $this;
    }

    /**
    @brief      設置多個列隊默認排隊數上限
    @param      Int         $num
    @return
    **/
    public function set_multi_max_num( $num = 0 )
    {
        $this->multi_exec_num = (int)$num;
        return $this;
    }

    /**
    @brief      用POST方式提交，支持多個URL
    @param      String      $url
    @param      String|Array    $vars
    @param      Array       $optionArr
    @param      Int         $timeout 超時時間，默認120秒
    @retval     String, false on failure
    @remarks    回傳模擬post蒐集的資料
    @code{.unparsed}
    $urls = array
    (
        'http://www.baidu.com/',
        'http://mytest.com/url',
        'http://www.abc.com/post',
    );
    $data = array
    (
         array('k1'=>'v1','k2'=>'v2'),
         array('a'=>1,'b'=>2),
         'aa=1&bb=3&cc=3',
    );
    $this->Core_Curl->post( $urls , $data );
    @endcode
    **/
    public function post( $url , $vars , $optionArr = '' , $timeout = 60 )
    {
        # ----------------------------------------------------------------------
        # POST模式
        # 如果是陣列擴充設定值不為空時，就另外設置
        # ----------------------------------------------------------------------
        if ( is_array( $optionArr ) )
        {
            foreach( $optionArr as $keys => $vals )
            {
                $this->set_option( $keys , $vals );
            }
        }
        else
        {
            $this->set_option( CURLOPT_HTTPHEADER, array('Expect:') );
            $this->set_option( CURLOPT_POST, true );
        }

        if ( is_array( $url ) )
        {
            $myvars = array();
            foreach ( $url as $k => $url )
            {
                if ( isset( $vars[$k] ) )
                {
                    if ( is_array( $vars[$k] ) )
                    {
                        $myvars[$url] = http_build_query( $vars[$k] );
                    }
                    else
                    {
                        $myvars[$url] = $vars[$k];
                    }
                }
            }
        }
        else
        {
            $myvars = array( $url => $vars );
        }
        $this->_post_data = $myvars;

        return $this->get( $url,$timeout );
    }

    /**
    @brief      GET方式獲取數據，支持多個URL
    @param      String|Array    $url
    @param      Int     $timeout
    @retval     String, false on failure
    @remarks    回傳模擬get蒐集的資料
    @code{.unparsed}
    $urls = array
    (
        'http://www.baidu.com/',
        'http://mytest.com/url',
        'http://www.abc.com/post',
    );
    $this->Core_Curl->get( $urls , 60 );
    @endcode
    **/
    public function get( $url , $timeout = 10 )
    {
        if ( is_array( $url ) )
        {
            $getone = false;
            $urls = $url;
        }
        else
        {
            $getone = true;
            $urls = array( $url );
        }

        $data = $this->request_urls( $urls , $timeout );

        $this->clear_set();

        if ( $getone )
        {
            $this->http_data = $this->http_data[$url];
            return $data[$url];
        }
        else
        {
            return $data;
        }
    }

    /**
    @brief      創建一個CURL對象
    @param      String    $url URL地址
    @param      Int       $timeout 超時時間
    @retval     String
    **/
    protected function _create( $url , $timeout )
    {
        # ----------------------------------------------------------------------
        # 檢查網址是否合法
        # ----------------------------------------------------------------------
        if ( filter_var( $url , FILTER_VALIDATE_URL ) === false )
        {
            $the_url = '';
        }
        else
        {
            $the_url = $url;
        }
        # ----------------------------------------------------------------------
        # 如果IP有設定
        # ----------------------------------------------------------------------
        if ( $this->ip )
        {
            # ------------------------------------------------------------------
            # 如果設置了IP，則把URL替換，然後設置Host的頭即可
            # ------------------------------------------------------------------
            if ( preg_match( '#^(http(?:s)?)\://([^/\:]+)(\:[0-9]+)?/#' , $the_url . '/' ,$m ) )
            {
                $this->header[] = 'Host: ' . $m[2];
                $the_url = $m[1] . '://' . $this->ip . $m[3] . '/' . substr( $the_url , strlen( $m[0] ) );
            }
        }

        $ch = curl_init();
        curl_setopt( $ch , CURLOPT_URL , $the_url) ;
        curl_setopt( $ch , CURLOPT_HEADER , true );
        curl_setopt( $ch , CURLOPT_FOLLOWLOCATION , false );
        curl_setopt( $ch , CURLOPT_RETURNTRANSFER , true );
        # ----------------------------------------------------------------------
        # CURLOPT_NOSIGNAL 設為 1，注意，使用毫秒超时一定要设置这个
        # ----------------------------------------------------------------------
        #curl_setopt( $ch , CURLOPT_NOSIGNAL , 1 );
        # ----------------------------------------------------------------------
        # 設定最長執行 900 毫秒，設為 0 表示不限制。
        # ----------------------------------------------------------------------
        #curl_setopt( $ch , CURLOPT_TIMEOUT_MS , 900 );
        # ----------------------------------------------------------------------
        # 設定允许执行的最长秒数，設為 0 表示不限制。
        # ----------------------------------------------------------------------
        curl_setopt( $ch , CURLOPT_TIMEOUT , $timeout );

        if ( preg_match( '#^https://#i' , $the_url ) )
        {
            curl_setopt( $ch , CURLOPT_SSL_VERIFYHOST , false );
            curl_setopt( $ch , CURLOPT_SSL_VERIFYPEER , false );
        }

        if ( $this->cookies )
        {
            curl_setopt( $ch , CURLOPT_COOKIE , http_build_query( $this->cookies , '' , ';' ) );
        }

        if ( $this->referer )
        {
            curl_setopt( $ch , CURLOPT_REFERER , $this->referer );
        }

        if ( $this->agent )
        {
            curl_setopt( $ch , CURLOPT_USERAGENT , $this->agent );
        }
        elseif ( array_key_exists( 'HTTP_USER_AGENT', $_SERVER ) )
        {
            curl_setopt( $ch , CURLOPT_USERAGENT , $_SERVER['HTTP_USER_AGENT'] );
        }

        foreach ( $this->_option as $k => $v )
        {
            curl_setopt( $ch , $k , $v );
        }

        if ( $this->header )
        {
            $header = array();
            foreach ( $this->header as $item )
            {
                # --------------------------------------------------------------
                # 防止有重複的header
                # --------------------------------------------------------------
                if ( preg_match( '#(^[^:]*):.*$#' , $item , $m ) )
                {
                    $header[$m[1]] = $item;
                }
            }
            curl_setopt( $ch, CURLOPT_HTTPHEADER , array_values( $header ) );
        }
        # ----------------------------------------------------------------------
        # 設置POST數據
        # ----------------------------------------------------------------------
        if ( isset( $this->_post_data[$the_url] ) )
        {
            curl_setopt( $ch , CURLOPT_POSTFIELDS , $this->_post_data[$the_url] );
        }

        return $ch;
    }

    /**
    @brief      支持多線程獲取網頁
    @see        http://cn.php.net/manual/en/function.curl-multi-exec.php#88453
    @param      Array|String    $urls 網址
    @param      Int     $timeout 逾時秒數
    @return     Array
    **/
    protected function request_urls( $urls, $timeout = 10 )
    {
        # ----------------------------------------------------------------------
        # 去重覆
        # ----------------------------------------------------------------------
        $urls = array_unique( $urls );

        if ( ! $urls ) return array();

        $mh = curl_multi_init();
        # ----------------------------------------------------------------------
        # 監聽列表
        # ----------------------------------------------------------------------
        $listener_list = array();
        # ----------------------------------------------------------------------
        # 返回值
        # ----------------------------------------------------------------------
        $result = array();
        # ----------------------------------------------------------------------
        # 總列隊數
        # ----------------------------------------------------------------------
        $list_num = 0;
        # ----------------------------------------------------------------------
        # 排隊列表
        # ----------------------------------------------------------------------
        $multi_list = array();
        foreach ( $urls as $url )
        {
            # ------------------------------------------------------------------
            # 創建一個curl對象
            # ------------------------------------------------------------------
            $current = $this->_create( $url , $timeout );

            if ( $this->multi_exec_num > 0 && $list_num >= $this->multi_exec_num )
            {
                # --------------------------------------------------------------
                # 加入排隊列表
                # --------------------------------------------------------------
                $multi_list[] = $url;
            }
            else
            {
                # --------------------------------------------------------------
                # 列隊數控制
                # --------------------------------------------------------------
                curl_multi_add_handle( $mh, $current );
                $listener_list[$url] = $current;
                $list_num++;
            }

            $result[$url] = null;
            $this->http_data[$url] = null;
        }
        unset( $current );

        $running = null;

        # ----------------------------------------------------------------------
        # 已完成數
        # ----------------------------------------------------------------------
        $done_num = 0;

        do
        {
            while ( ( $execrun = curl_multi_exec( $mh , $running ) ) == CURLM_CALL_MULTI_PERFORM );
            if ( $execrun != CURLM_OK ) break;

            while ( true == ( $done = curl_multi_info_read( $mh ) ) )
            {
                foreach ( $listener_list as $done_url=>$listener )
                {
                    if ( $listener === $done['handle'] )
                    {
                        # ------------------------------------------------------
                        # 獲取內容
                        # ------------------------------------------------------
                        $this->http_data[$done_url] = $this->get_data( curl_multi_getcontent( $done['handle'] ) , $done['handle'] );

                        if ( $this->http_data[$done_url]['code'] != 200 )
                        {
                            $result[$done_url] = false;
                        }
                        else
                        {
                            # --------------------------------------------------
                            # 返回內容
                            # --------------------------------------------------
                            $result[$done_url] = $this->http_data[$done_url]['data'];
                        }

                        curl_close( $done['handle'] );

                        curl_multi_remove_handle( $mh , $done['handle'] );
                        # ------------------------------------------------------
                        # 把監聽列表裡移除
                        # ------------------------------------------------------
                        unset( $listener_list[$done_url] , $listener );
                        $done_num++;
                        # ------------------------------------------------------
                        # 如果還有排隊列表，則繼續加入
                        # ------------------------------------------------------
                        if ( $multi_list )
                        {
                            # --------------------------------------------------
                            # 獲取列隊中的一條URL
                            # --------------------------------------------------
                            $current_url = array_shift( $multi_list );
                            # --------------------------------------------------
                            # 創建CURL對象
                            # --------------------------------------------------
                            $current = $this->_create( $current_url , $timeout );
                            # --------------------------------------------------
                            # 加入到列隊
                            # --------------------------------------------------
                            curl_multi_add_handle( $mh , $current );
                            # --------------------------------------------------
                            # 更新監聽列隊信息
                            # --------------------------------------------------
                            $listener_list[$current_url] = $current;
                            unset($current);
                            # --------------------------------------------------
                            # 更新列隊數
                            # --------------------------------------------------
                            $list_num++;
                        }

                        break;
                    }
                }
            }

            if ($done_num>=$list_num)break;

        } while (true);
        # ----------------------------------------------------------------------
        # 關閉列隊
        # ----------------------------------------------------------------------
        curl_multi_close($mh);

        return $result;
    }

    /**
    @brief      重新包裝 Curl 的資料
    @param      $data       curl_multi_getcontent
    @param      $ch         curl_init
    @return     Array
    **/
    protected function get_data( $data , $ch )
    {
        $header_size      = curl_getinfo( $ch , CURLINFO_HEADER_SIZE );
        $result['code']   = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
        $result['data']   = substr( $data , $header_size );
        $result['header'] = explode( "\r\n" , substr( $data , 0 , $header_size ) );
        $result['time']   = curl_getinfo( $ch , CURLINFO_TOTAL_TIME );

        return $result;
    }

    /**
    @brief 清理設置
    **/
    protected function clear_set()
    {
        $this->_option = array();
        $this->header = array();
        $this->ip = null;
        $this->cookies = null;
        $this->referer = null;
        $this->_post_data = array();
    }
}