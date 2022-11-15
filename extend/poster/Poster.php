<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 海报生成
 */
namespace poster;

class Poster
{
    
    public $bg; // 背景图

    /**
     * 构造函数
     * @param $w int 宽度(px)
     * @param $w int 高度(px)
     * @param $bg_color array RGB color value
     */
    public function __construct(int $w, int $h, $bg_color = [100, 150, 150])
    {
        $this->createBg($w, $h, $bg_color);
    }

    /**
     * 填充画布背景
     * @param $w int 宽度(px)
     * @param $w int 高度(px)
     * @param $bg_color array [R,G,B] color value
     */
    protected function createBg($w, $h, $bg_color)
    {
        $this->bg = imagecreatetruecolor($w, $h);
        $c = imagecolorallocate($this->bg, $bg_color[0], $bg_color[1], $bg_color[2]);
        imagefill($this->bg, 0, 0, $c);
    }

    /**
     * 添加图片
     * @param $img_path string 图片路径
     * @param $xy array 坐标[x坐标，y坐标]
     * @param $size_wh array 尺寸[width, height]
     * @param $degrees 选择角度
     */
    public function addImg($img_path, $xy = [0, 0], $size = [100, 100],$degrees=0)
    {
        list($l_w, $l_h) = getimagesize($img_path);
        $img = $this->createImageFromFile($img_path);
        if($degrees > 0){
            $img = imagerotate($img, $degrees, 0);
        }
        imagecopyresized($this->bg, $img, $xy[0], $xy[1], 0, 0, $size[0], $size[1], $l_w, $l_h);
        imagedestroy($img);
        return $this;
    }

    /**
     * 添加文字
     * @param $text string 文字
     * @param $size int 文字大小
     * @param $xy array 坐标[x坐标，y坐标]
     * @param $color array [R,G,B] color value
     * @param $font_ file string 字体路径
     * @param $degrees int 文字旋转角度
     */
    public function addText($text, $size = 14, $xy = [0, 0], $color = [0, 0, 0],$degrees = 0,$font_file = '')
    {
        if($font_file == '') $font_file = __DIR__ . DIRECTORY_SEPARATOR . 'poster.otf';
        $font_color = ImageColorAllocate($this->bg, $color[0], $color[1], $color[2]);
        imagettftext($this->bg, $size, $degrees, $xy[0], $xy[1], $font_color, $font_file, $text);
        return $this;
    }
    
    /**
    * 输出图片
    * @param $file_name string 最后保存海报的路径，留空表示直接向浏览器输出图片
    * @param $degrees 选择角度
    */
    public function render($file_name='',$degrees = 0)
    {
        $img = imagerotate($this->bg,$degrees, 0);
        if ($file_name != '') {
            imagepng($img,$file_name);
        } else {
            Header("Content-Type: image/png");
            imagepng($img);
        }
        imagedestroy($img);
        imagedestroy($this->bg);
    }
    
    /**
     * 从图片文件创建Image资源
     * @param $file 图片文件，支持url
     * @return bool|resource   成功返回图片image资源，失败返回false
     */
    public function createImageFromFile($file)
    {
        if (preg_match('/http(s)?:\/\//', $file)) {
            $fileSuffix = $this->getNetworkImgType($file);
        } else {
            $fileSuffix = pathinfo($file, PATHINFO_EXTENSION);
        }
        if (!$fileSuffix) return false;
        switch ($fileSuffix) {
            case 'jpeg':
                $theImage = @imagecreatefromjpeg($file);
                break;
            case 'jpg':
                $theImage = @imagecreatefromjpeg($file);
                break;
            case 'png':
                $theImage = @imagecreatefrompng($file);
                break;
            case 'gif':
                $theImage = @imagecreatefromgif($file);
                break;
            default:
                $theImage = @imagecreatefromstring(file_get_contents($file));
                break;
        }
        return $theImage;
    }
    
    /**
     * 获取网络图片类型
     * @param $url  网络图片url,支持不带后缀名url
     * @return bool
     */
    public function getNetworkImgType($url)
    {
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url); //设置需要获取的URL
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //支持https
        curl_exec($ch); // 执行curl会话
        $http_code = curl_getinfo($ch); //获取curl连接资源句柄信息
        curl_close($ch); // 关闭资源连接
        if ($http_code['http_code'] == 200) {
            $theImgType = explode('/', $http_code['content_type']);
            if ($theImgType[0] == 'image') {
                return $theImgType[1];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}