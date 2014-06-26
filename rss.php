<?php
require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/deal.php';
require './app/Lib/message.php';
require_once './system/libs/rss.php';
require_once './app/Lib/side.php';

 $rss = new UniversalFeedCreator();   
 $rss->useCached(); // use cached version if age<1 hour  
 $rss->title = app_conf("SHOP_TITLE")." - ".app_conf("SHOP_SEO_TITLE");   
 $rss->description = app_conf("SHOP_SEO_TITLE"); 
   
 //optional  
 $rss->descriptionTruncSize = 500;  
 $rss->descriptionHtmlSyndicated = true;  
   
 $rss->link = get_domain().APP_ROOT;   
 $rss->syndicationURL = get_domain().APP_ROOT;   
   

   
 //optional  
 $image->descriptionTruncSize = 500;  
 $image->descriptionHtmlSyndicated = true;  
   

 $domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?get_domain().$GLOBALS['IMG_APP_ROOT']:app_conf("PUBLIC_DOMAIN_ROOT");
 	
        
        
 $city = get_current_deal_city();
 $city_id = $city['id'];
 $deal_list = get_deal_list(app_conf("SIDE_DEAL_COUNT"),0,$city_id,array(DEAL_ONLINE)," buy_type <> 1 ");
 $deal_list = $deal_list['list'];

 foreach($deal_list as $data) {   
     $item = new FeedItem();   
     $item->title = msubstr($data['name'],0,30);   
     $item->link = get_domain().$data['url'];  

     $data['description'] = str_replace($GLOBALS['IMG_APP_ROOT']."./public/",$domain."/public/",$data['description']);	
     $data['description'] = str_replace("./public/",$domain."/public/",$data['description']);
        
     $data['img'] = str_replace("./public/",$domain."/public/",$data['img']);
     $item->description =  "<img src='".$data['img']."' /><br />".$data['brief']."<br /> <a href='".get_domain().$data['url']."' target='_blank' >".$GLOBALS['lang']['VIEW_DETAIL']."</a>";   
       
     //optional  
     $item->descriptionTruncSize = 500;  
     $item->descriptionHtmlSyndicated = true;  

     if($data['end_time']!=0)
     $item->date = date('r',$data['end_time']);   
     $item->source = $data['url'];   
     $item->author = app_conf("SHOP_TITLE");   
  
        
     $rss->addItem($item);   
 }
 
 
 $rss->saveFeed($format="RSS0.91", $filename=APP_ROOT_PATH."app/Runtime/tpl_caches/rss.xml");
?> 