<?php

if(!empty($_GET['ajax']) && $_GET['ajax'] == 'file' && !empty($_POST['type']))
{
    /*echo '<pre>';
    print_r($_POST);
    echo '</pre> files:<pre>';
    print_r($_FILES);*/
    //die(json_encode($_POST));
   
    
        
    //if($_POST['type'] == 'product') die(json_encode($_POST));
        
    if(count($_FILES) < 1)die('no array in $_FILES');
    
    
    include_once('images.php');
   /* include_once('../../config.php');
    include_once('../lib/core.class.php');
    include_once('../lib/mysql.class.php');    
    
    $pCore = new CCore;
        */
    $newName = array();    
    foreach($_FILES['files']['tmp_name'] as $k => $tmp)
    {
        if($_POST['type'] == 'top')
        {
            //$newName[$k] = $_FILES['files']['name'][$k];
            $newName[$k] = uniqid().'-'. rand(100,999) .'.jpg';
            //move_uploaded_file($tmp, '/var/www/startup/uploads/logo/'. iconv('utf-8','cp1251',$newName[$k]));
            img_resize($tmp, '../../upd/bnr/'. iconv('utf-8','cp1251',$newName[$k]), 544, 168);
        }
        elseif($_POST['type'] == 'left'){
            $newName[$k] = uniqid().'-'. rand(100,999) .'.jpg';
            img_resize($tmp, '../../upd/bnr/short/'. iconv('utf-8','cp1251',$newName[$k]), 197, 173);
        }
        
       // $pCore->table('cs_sliders')->where('id='.((int)$_POST['code']))->update(array('img'=>$newName[$k]));
    }
    die(json_encode($newName));
}
elseif(count($_POST)) 
{
    echo '<pre>';
    print_r($_POST);
    exit();
}

?>