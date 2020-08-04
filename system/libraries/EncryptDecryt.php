<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        資料加密類，可逆加密演算法。
  @version      1.0.1
  @date         2015-05-07
  @since        1.0.1 -> 針對『PHP5.5』處理也相容『PHP5.3』。
  @note         升级到『PHP5.5』後，mcrypt_cbc()、mcrypt_cfb()、mcrypt_ecb()、mcrypt_ofb() 廢除了\n
                - 经过代码修改，其中mcrypt_cbc()的替代方案如下：
                - 例如：mcrypt_cbc(MCRYPT_DES, $key, $str, MCRYPT_ENCRYPT, $iv);
                - 替代：mcrypt_encrypt(MCRYPT_DES, $key, $str, 'cbc', $iv)

                - 例如：mcrypt_cbc(MCRYPT_DES, $key, $strBin, MCRYPT_DECRYPT, $iv);
                - 替代：mcrypt_decrypt(MCRYPT_DES, $key, $strBin, 'cbc', $iv);
  @since        1.0.0 -> 新增此新類別。
  @pre          請於各專案下的 DEVIL_APP_CRONKEY 設定金鑰。
  @attention    注意: 此類別由系統自動初始化所以不需要手動載入。
**/

class EncryptDecryt
{
    /**
    加解密用金鑰
    **/
    private $key;

    /**
    偏移量
    **/
    private $iv;

    /**
    @cond       建構子
    @remarks    初始化金鑰、偏移量。
    **/
    public function __construct()
    {
        # ----------------------------------------------------------------------
        # 設定偏移量陣列
        # ----------------------------------------------------------------------
        $_IV = array( 78 , 214 , 48 , 76 , 9 , 61 , 244 , 246 );
        # ----------------------------------------------------------------------
        # 設定金鑰
        # ----------------------------------------------------------------------
        $this->key = DEVIL_APP_CRONKEY;
        # ----------------------------------------------------------------------
        # 轉成文字型態
        # ----------------------------------------------------------------------
        $this->iv = implode( '' , array_map( "chr" , $_IV ) );
    }
    /**
    @endcond
    **/

    /**
    @brief      加密
    @param      String  $str
    @pre        測試的金鑰為：simple
    @remarks    此範例結果：9799A16830AEE3ED。
    @code{.unparsed}
    $this->Core_EncryptDecryt->encrypt( '123' );
    @endcode
    **/
    public function encrypt( $str )
    {
        # ----------------------------------------------------------------------
        # 轉成unicode,詳情請參考http://www.unicode.org/faq/utf_bom.html
        # C#是用 Encode.Unicode.GetBytes(...) 轉的,php要用 UTF-16LE:little-endian
        # ----------------------------------------------------------------------
        # $str = iconv( 'utf-8' , 'UTF-16LE' , $str );
        $size = mcrypt_get_block_size( MCRYPT_DES , MCRYPT_MODE_CBC );
        $str = self::pkcs5Pad( $str , $size );
        return strtoupper( bin2hex( mcrypt_encrypt( MCRYPT_DES , $this->key , $str , 'cbc', $this->iv ) ) );
    }

    /**
    @brief      解密
    @param      String  $str
    @pre        測試的金鑰為：simple
    @remarks    此範例結果：123。
    @code{.unparsed}
    $this->Core_EncryptDecryt->decrypt( '9799A16830AEE3ED' );
    @endcode
    **/
  	public function decrypt( $str )
    {
        $strBin = self::hex2bin( strtolower( $str ) );
        $str = mcrypt_decrypt( MCRYPT_DES , $this->key , $strBin , 'cbc' , $this->iv );
        $str = self::pkcs5Unpad( $str );
        return $str;
    }

    /**
    @brief      把字符串十六進制轉成二進制
    @param      String      ( $hexData 內容資料 )
    **/
    private function hex2bin( $hexData )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $binData = "";
        $hexData_length = strlen( $hexData );
        for ( $i = 0; $i < $hexData_length; $i += 2 )
        {
            $binData .= chr( hexdec( substr( $hexData , $i , 2 ) ) );
        }
        return $binData;
    }

    /**
    @brief      產生自定義字符填充
    @param      String      ( $text )
    @param      Int         ( $blocksize )
    @return     String      ( 字符串重复指定的次數 )
    **/
    private function pkcs5Pad( $text , $blocksize )
    {
        $pad = $blocksize - ( strlen( $text ) % $blocksize );
        return $text . str_repeat( chr( $pad ) , $pad );
    }

    /**
    @brief      解開自定義字符填充
    @param      String      ( $text )
    @return     String
    **/
    private function pkcs5Unpad( $text )
    {
        $pad = ord( $text { strlen( $text ) - 1 } );
        if ( $pad > strlen( $text ) )
        {
            return false;
        }
        if ( strspn( $text , chr( $pad ) , strlen( $text ) - $pad ) != $pad )
        {
            return false;
        }
        return substr( $text, 0, - 1 * $pad );
    }
}