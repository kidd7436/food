<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        Arrays 用來處理陣列相關的功能類別。
  @version      1.0.0
  @date         2015-03-18
  @since        1.0.0 -> 新增此新類別。
**/

class Arrays
{
    /**
    @brief      將字串轉陣列並且降冪排序、升冪排序
    @param      String      $_str 字串
    @param      String      $_type 排序的方式，預設為 sort
    @retval     Type
    **/
    public function str2arr_sort( $_str , $_type = 'sort' )
    {
        $_TempArr = str_split( $_str );
        switch ( $_type )
        {
            case "sort":
                sort( $_TempArr );
                break;
            case "rsort":
                rsort( $_TempArr );
                break;
        }
        return implode( '' ,  $_TempArr );
    }

    /**
    @brief      將二維陣列內的子陣列[1,2]轉成物件型態[1:2],回傳一維物件
    @param      Array       $_arr
    @retval     Obj | Array
    **/
    public function doubleArr_2_obj( $_arr = array() )
    {
        $_TempArr = array();
        if ( count( $_arr ) == 0 )
        {
            return (object)array();
        }
        foreach( $_arr as $valArr )
        {
            $_TempArr += array( $valArr[0] => $valArr[1] );
        }
        return $_TempArr;
    }

    /**
    @brief      將二維元素轉一維
    @param      Array       $array
    @retval     Array
    **/
    public function arr2To1( $array )
    {
        $reArr = array();
        foreach( $array as $arrKey => $arrVal )
        {
            if ( is_array( $arrVal ) )
            {
                foreach( $arrVal as $key => $val )
                {
                    $reArr[] = $val;
                }
            }
            else
            {
                return $arrVal;
            }
        }
        return $reArr;
    }

    /**
    @brief      從陣列中讀取元素，此函數會檢查陣列索引是否已設定，且陣列值是否存在。
                若存在則傳回陣列值，否則傳回false或是任何你所指定的預設值（透過函數第三個參數設定）
    @param      String      $item
    @param      Array       $array
    @param      Mixed       $default
    @return     mixed       depends on what the array contains
    **/
    public function element( $item , $array , $default = false )
    {
        if ( ! isset( $array[$item] ) || $array[$item] == "" )
        {
            return $default;
        }
        return $array[$item];
    }

    /**
    @brief      讓你獲取一個從一個數組中的項目數。功能測試是否設置每個數組的索引。
                如果索引不存在，它被設置為false，或任何你指定通過第三個參數為默認值
    @param      Array       $items
    @param      Array       $array
    @param      Mix         $default
    @return     Mix         depends on what the array contains
    **/
    public function elements( $items , $array , $default = false )
    {
        $return = array();

        if ( ! is_array( $items ) )
        {
            $items = array( $items );
        }

        foreach ( $items as $item )
        {
            if ( isset( $array[$item] ) )
            {
                $return[$item] = $array[$item];
            }
            else
            {
                $return[$item] = $default;
            }
        }
        return $return;
    }

    /**
    @brief      隨機傳回一該陣列之元素
    @param      Array       $array
    @return     mixed       depends on what the array contains
    **/
    public function random_element( $array )
    {
        if ( ! is_array( $array ) )
        {
            return $array;
        }
        return $array[array_rand($array)];
    }

    /**
    @brief      刪除指定陣列內的值
    @param      Array       $array
    @param      Mix         $val
    @return     Array
    **/
    public function unset_Specified_Vals( $array , $val )
    {
        $n = sizeof( $array );
        for ( $i = 0; $i < $n; $i++ )
        {
            switch ( gettype( $val ) )
            {
                case "integer":
                    if ( $array[$i] == $val ) unset( $array[$i] );
                    break;
                case "string":
                    if ( ! strcmp( $array[$i], $val ) ) unset( $array[$i] );
                    break;
                default:
                    die('尚未處理此型態，請增加' . gettype( $val ) . '判斷。');
            }
        }
        return $array;
    }

    /**
    @brief      從多維陣列中尋找指定的 值 有沒有存在
    @param      String      $value
    @param      Array       $array
    @return     Bool
    **/
    public function multi_inarray( $value , $array )
    {
        if ( ! is_array( $array ) ) return false;

        foreach ( $array as $item )
        {
            if ( ! is_array( $item ) )
            {
                if ( $item == $value )
                {
                    return true;
                }
                continue;
            }

            if ( in_array( $value , $item ) )
            {
                return true;
            }
            elseif ( self::multi_inarray( $value , $item ) )
            {
                return true;
            }
        }
        return false;
    }

    /**
    @brief      傳回 指定的Key值 在陣列的 ?,?,? 的位置
    @param      String      $needle
    @param      Array       $haystack
    @param      Array       $path
    @return     Array / false
    **/
    public function arraysearch_recursive( $needle , $haystack , $path = array() )
    {
        foreach ( $haystack as $id => $val )
        {
            $path2 = $path;
            $path2[] = $id;

            if ( $val === $needle )
            {
                return $path2;
            }
            elseif ( is_array( $val ) )
            {
                if ( $ret = self::arraysearch_recursive( $needle , $val , $path2 ) )
                {
                    return $ret;
                }
            }
        }

        return false;
    }

    /**
    @brief      在多維陣列中刪除指定的 值
    @param      Array       $array
    @param      String      $val
    @return     Array / false
    **/
    public function recursiveRemoval( &$array , $val )
    {
        if ( is_array( $array ) )
        {
            foreach( $array as $key => &$arrayElement )
            {
                if ( is_array( $arrayElement ) )
                {
                    self::recursiveRemoval( $arrayElement , $val );
                }
                else
                {
                    if ( $arrayElement == $val )
                    {
                        unset( $array[$key] );
                    }
                }
            }
            return $array;
        }
        else
        {
            return false;
        }
    }

    /**
    @brief      二維陣列排序
    @param      Array       $arr
    @param      String      $keys
    @param      String      $type
    @return     Array | false       $type
    **/
    public function array2_sort( $arr , $keys , $type = 'asc' )
    {
        # ------------------------------------------------------------------
        # 初始化變數
        # ------------------------------------------------------------------
        $keysvalue = $new_array = array();
        # ------------------------------------------------------------------
        # 先整理一次陣列資料
        # ------------------------------------------------------------------
        foreach( $arr as $k => $v )
        {
            $keysvalue[$k] = $v[$keys];
        }
        # ------------------------------------------------------------------
        # 排序
        # ------------------------------------------------------------------
        if ( $type == 'asc' )
        {
            asort( $keysvalue );
        }
        else
        {
            arsort( $keysvalue );
        }
        # ------------------------------------------------------------------
        # 函數把數組的內部指針指向第一個元素，並返回這個元素的值。
        # ------------------------------------------------------------------
        reset( $keysvalue );
        # ------------------------------------------------------------------
        # 重新整理一次陣列值
        # ------------------------------------------------------------------
        foreach ( $keysvalue as $k => $v )
        {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

}