<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.09.2015
 */
namespace skeeks\cms\kladr\assets;
use skeeks\cms\base\AssetBundle;

/**
 * Class Asset
 * @package skeeks\modules\cms\kladr\assets
 */
class Asset extends AssetBundle
{
    public $sourcePath = '@skeeks/cms/kladr/assets';

    public $css = [];
    public $js = [];
    public $depends = [];
}
