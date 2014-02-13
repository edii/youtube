<?php
/**
 * @package image
 *
 * @author Андрей Загорцев <freeron@ya.ru>
 * @author Антон Кургузенков <kurguzenkov@list.ru>
 *
 * @version 2.0.1
 * @since 2013-03-12
 */
//namespace core\imagetoolkit\ResizeImages {
//use core\imagetoolkit\AcImage as AcImage;
// require_once '/imagetoolkit/AcImage.php';

require_once 'AcImage.php';

class ResizeImages extends core\imagetoolkit\AcImage {
    function __construct($filePath) {
        parent::__construct($filePath);
    }
}

//}