<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        cookie 操作類，將所有 cookie 操作集中至此。
  @version      1.0.0
  @date         2015-02-27
  @since        1.0.0 -> 新增此新類別。
  @attention    注意: 此類別由系統自動初始化所以不需要手動載入。
**/

class Cookie
{
    /**
    @brief      获取cookie
    @param      String      $name
    @return     Mixed
    @remarks    此範例結果：返回名為 Test 的cookie值。
    @code{.unparsed}
    $this->Core_Cookie->get( 'Test' );
    @endcode
    **/
    public function get( $name = '' )
    {
        if ( $name == '' ) return false;
        return isset( $_COOKIE[$name] ) ? $_COOKIE[$name] : false;
    }

    /**
    @brief      设置cookie
    @param      String      $name cookie名称
    @param      Mixed       $value cookie值
    @param      Int         $expire 有效值( 時間戳記 )
    @param      String      $path cookie作用路径
    @param      String      $domain 默认为当前域名
    @remarks    此範例結果：設置一個cookie 名為 Test 值為istest。
    @code{.unparsed}
    $this->Core_Cookie->set( 'Test' , istest , 3600 );
    @endcode
    @note
    PHP setcookie() 在Chrome失效。。
    在本地开发时（使用 localhost域名），使用PHP的 setcookie( 'name', 'value', $expire, '/', 'localhost' ); 设置Cookie，然后调用 var_dump( $_COOKIE ); 打印Cookie，会发现打印一个空的数组，证明 setcookie()失败。这种情况在FF里没出现，但在Chrome就出现。。
    后来看到 http://stackoverflow.com/questions/1829147/php-cookie-path-doesnt-work 里面别人的评论：
    ( Are you testing on localhost? In that case, you need to pass null as the value for $domain. )
    原来使用localhost作为$domain参数的话，Chrome会出现 setcookie 失败的情况，换上 null 以后，一切解决。。
    **/
    public function set( $name , $value , $expire = 604800 , $path = '/' , $domain = null )
    {
        # ----------------------------------------------------------------------
        # 如果是 Server Cookie
        # ----------------------------------------------------------------------
        if ( $expire == 0 )
        {
            setcookie( $name , $value , 0 , '/' );
        }
        else
        {
            # ------------------------------------------------------------------
            # 如果當前 domain 不是 localhost 才使用
            # ------------------------------------------------------------------
            if ( $domain === null && $_SERVER['HTTP_HOST'] != 'localhost' )
            {
                $domain = '.' . $_SERVER['HTTP_HOST'];
            }
            setcookie( $name , $value , $expire + time() , $path , $domain );
        }
        # ----------------------------------------------------------------------
        # 使cookie马上生效
        # ----------------------------------------------------------------------
        $_COOKIE[$name] = $value;
    }

    /**
    @brief      删除cookie
    @param      string      $name cookie名称
    @remarks    此範例結果：設置一個cookie 名為 Test 值為istest。
    @code{.unparsed}
    $this->Core_Cookie->delete( 'Test' );
    @endcode
    **/
    public function delete( $name )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $domain = null;
        # ----------------------------------------------------------------------
        # 如果當前 domain 不是 localhost 才使用
        # ----------------------------------------------------------------------
        if ( $_SERVER['HTTP_HOST'] != 'localhost' )
        {
            $domain = '.' . $_SERVER['HTTP_HOST'];
        }
        # ----------------------------------------------------------------------
        # 刪除一般 Cookie
        # ----------------------------------------------------------------------
        setcookie( $name , null , time() - 3600 , '/' , $domain );
        # ----------------------------------------------------------------------
        # Server Cookie 也刪除
        # ----------------------------------------------------------------------
        setcookie( $name , null , time() - 3600 , '/' );
        # ----------------------------------------------------------------------
        # 再刪除一次陣列值中的 Cookie
        # ----------------------------------------------------------------------
        unset( $_COOKIE[$name] );
    }

    /**
    @brief      删除所有的cookies
    @remarks    此範例結果：清除所有cookie。
    @code{.unparsed}
    $this->Core_Cookie->deleteAll();
    @endcode
    **/
    public function deleteAll()
    {
        foreach ( $_COOKIE as $k => $v )
        {
            self::delete( $k );
        }
    }
}