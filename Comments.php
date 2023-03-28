<?php
require_once 'connect_db.php';

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    if (!is_numeric($id)) {
        header('location: /404/');
        die();
    }

    $query = "SELECT * FROM articles WHERE id=?";
    $stmt = mysqli_prepare($connect_db, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $query_result = mysqli_stmt_get_result($stmt);
    $post = mysqli_fetch_assoc($query_result);

    if(empty($post)) {
        header('location: /404/');
        die();
    }

    // Check if the author of the article is suspended
    $author_id = $post['author_id'];
    $author_query = "SELECT * FROM users WHERE author_id = $author_id";
    $author_result = mysqli_query($connect_db, $author_query);
    $author = mysqli_fetch_assoc($author_result);

    // Check if the user is logged in and is the author of the post
    $is_author = isset($_SESSION['user-id']) && $_SESSION['user-id'] == $post['author_id'];

    // Check if the article is public or if the user is the author or if the user is logged in
    if($post['status'] == 2 || $post['status'] == 3 || $is_author || isset($_SESSION['user-id'])) {

        // Check if the article is private and the user is not logged in or is not the author
        if(($post['status'] == 1 && (!$is_author && !isset($_SESSION['user-id']))) || ($author['status'] == 1 && (!$is_author && !isset($_SESSION['user-id'])))) {
            header('location: /404/');
            die();
        }

        $query = "SELECT * FROM articles WHERE id=?";
        $stmt = mysqli_prepare($connect_db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $query_result = mysqli_stmt_get_result($stmt);
        $display = mysqli_fetch_assoc($query_result);

        if(empty($display)) {
            header('location: /404/');
            die();
        }

        $old_date = htmlspecialchars($display['date_time'], ENT_QUOTES|ENT_SUBSTITUTE);
        $new_date = date("d.m.Y", strtotime($old_date));
    } else {
        header('location: /404/');
        die();
    }
}
?>
<!DOCTYPE html>
<html lang="<?= website_language ?>" dir="<?= website_dir ?>">
<head>
<?php $query="SELECT * FROM system_options WHERE id=16";$result=mysqli_query($connect_db,$query);while($row=mysqli_fetch_array($result))if($row['display']==""){echo"";}else{echo''.$row["display"].'';}?>
<title><?php echo $display['title']; ?> <?= header_separator ?> <?= website_name ?></title>
<?php
if ($post['status'] == 3 && $post['approved'] == 1 && $author['status'] == 3) {
    $query = "SELECT * FROM system_options WHERE id = 60";
} else {
    $query = "SELECT * FROM system_options WHERE id = 65";
}

$stmt = mysqli_prepare($connect_db, $query);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!empty($result)) {
    $row = mysqli_fetch_array($result);

    if ($row['display'] == '') {
        echo "";
    } else {
        echo $row['display'];
    }
}
?>
<meta name="description" content="<?php echo $display['subtitle'] ?>">
<meta name="author" content="<?php echo $author['firstname'];?> <?php echo $author['lastname'];?>">
<link rel="canonical" href="<?php echo main_url.$_SERVER['REQUEST_URI'];?>">
<script src="/files/js/article.min.js<?= website_version ?>"></script>
<link rel="stylesheet" type="text/css" href="/files/css/style.min.css<?= website_version ?>">
<?php $query="SELECT * FROM system_options WHERE id=28";$result=mysqli_query($connect_db,$query);while($row=mysqli_fetch_array($result))if($row['display']==""){echo"";}else{echo'<meta property="fb:admins" content="'.$row["display"].'">';}?>
<?php $query="SELECT * FROM system_options WHERE id=8";$result=mysqli_query($connect_db,$query);while($row=mysqli_fetch_array($result))if($row['display']==""){echo"";}else{echo'<meta property="fb:app_id" content="'.$row["display"].'">';}?>
<meta property="og:title" content="<?php echo $display['title'] ?> <?= header_separator ?> <?= website_name ?>">
<?php $query="SELECT * FROM system_options WHERE id=4";$result=mysqli_query($connect_db,$query);while($row=mysqli_fetch_array($result))if($row['display']==""){echo"";}else{echo'<meta property="og:locale" content="'.$row["display"].'">';}?>
<meta property="og:type" content="<?php $query="SELECT * FROM system_options WHERE id=25";$result=mysqli_query($connect_db,$query);while($row=mysqli_fetch_array($result)){echo ''.$row["display"].'';}?>">
<meta property="og:url" content="<?php echo main_url.$_SERVER['REQUEST_URI'];?>">
<meta property="og:image" content="<?= main_url ?>/files/images/articles/<?php echo $display['id'] ?>_mini.jpg<?= website_version ?>">
<meta property="og:image:width" content="<?php $query="SELECT * FROM system_options WHERE id=23";$result=mysqli_query($connect_db,$query);while($row=mysqli_fetch_array($result)){echo ''.$row["display"].'';}?>">
<meta property="og:image:height" content="<?php $query="SELECT * FROM system_options WHERE id=24";$result=mysqli_query($connect_db,$query);while($row=mysqli_fetch_array($result)){echo ''.$row["display"].'';}?>">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:alt" content="<?php echo $display['title'] ?> <?= header_separator ?> <?= website_name ?>">
<meta property="og:description" content="<?php echo $display['subtitle'] ?>">
<meta property="og:site_name" content="<?= website_name ?>">
<meta property="article:author" content="<?php echo $author['firstname'];?> <?php echo $author['lastname'];?>">
<meta property="article:published_time" content="<?php $old_published_time=$display["date_time"];
$published_time=date("Y-m-d",strtotime($old_published_time));echo $published_time?>T<?php $old_published_time=$display["date_time"];
$published_time=date("H:i:s+00:00",strtotime($old_published_time));echo $published_time?>">
<meta property="article:modified_time" content="<?php $old_published_time=$display["date_time"];
$published_time=date("Y-m-d",strtotime($old_published_time));echo $published_time?>T<?php $old_published_time=$display["date_time"];
$published_time=date("H:i:s+00:00",strtotime($old_published_time));echo $published_time?>">
<meta property="article:section" content="Article Section">
<?php $category_id=$display['category_id'];$categories_query="SELECT * FROM categories WHERE id=$category_id";$categories_result=mysqli_query($connect_db,$categories_query);$categories=mysqli_fetch_assoc($categories_result); ?>
<meta property="article:tag" content="<?= $categories['title'] ?>">
<meta name="twitter:url" content="<?php echo main_url.$_SERVER['REQUEST_URI'];?>">
<?php $query="SELECT * FROM system_options WHERE id=9";$result=mysqli_query($connect_db,$query);while($row=mysqli_fetch_array($result))if($row['display']==""){echo"";}else{echo'<meta name="twitter:card" content="'.$row["display"].'">';}?>
<?php $query="SELECT * FROM system_options WHERE id=10";$result=mysqli_query($connect_db,$query);while($row=mysqli_fetch_array($result))if($row['display']==""){echo"";}else{echo'<meta name="twitter:site" content="'.$row["display"].'">';}?>
<?php if($author['twitter_username']==""){echo"";}else{echo'<meta name="twitter:creator" content="@'.$author["twitter_username"].'">';}?>
<meta name="twitter:title" content="<?php echo $display['title'] ?> <?= header_separator ?> <?= website_name ?>">
<meta name="twitter:description" content="<?php echo $display['subtitle'] ?>">
<meta name="twitter:image" content="<?= main_url ?>/files/images/articles/<?php echo $display['id'] ?>_mini.webp">
<meta name="twitter:image:alt" content="<?php echo $display['title'] ?> <?= header_separator ?> <?= website_name ?>">
<meta name="twitter:label1" content="Written by">
<meta name="twitter:data1" content="<?php echo $author['firstname'];?> <?php echo $author['lastname'];?>">
<meta name="twitter:label2" content="Est. reading time">
<meta name="twitter:data2" content="<?php echo $display['reading_time'] ?> דקות">
<meta name="apple-mobile-web-app-title" content="<?= website_name ?>">
<meta name="application-name" content="<?= website_name ?>">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-config" content="/browserconfig.xml<?= website_version ?>">
<meta name="theme-color" content="#ffffff">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png<?= website_version ?>">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png<?= website_version ?>">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png<?= website_version ?>">
<link rel="manifest" href="/manifest.json<?= website_version ?>">
<link rel="mask-icon" href="/safari-pinned-tab.svg<?= website_version ?>" color="#f79321">
<link rel="shortcut icon" href="/favicon.ico<?= website_version ?>">
<?php $query="SELECT * FROM system_options WHERE id=45";$result=mysqli_query($connect_db,$query);while($row=mysqli_fetch_array($result)){echo ''.$row["display"].'';}?>
<script>
const images=document.querySelectorAll(".lazy-image"),imageObserver=new IntersectionObserver((e,s)=>{e.forEach(e=>{if(e.isIntersecting){const s=e.target;s.src=s.dataset.src,s.classList.remove("lazy-image"),imageObserver.unobserve(s)}})});
$(window).load(function(){$(".sp-wrap").smoothproducts()});
</script>
<script type="application/ld+json">
{"@context":"https://schema.org","@graph":[{"@type":"Article","@id":"<?php echo main_url.$_SERVER['REQUEST_URI'];?>#article","isPartOf":{"@id":"<?php echo main_url.$_SERVER['REQUEST_URI'];?>"},"author":{"url":"<?= main_url ?>/author/<?php echo $author['username']?>/","name":"<?php echo $author['firstname'];?> <?php echo $author['lastname'];?>","@id":"<?= main_url ?>/author/<?php echo $author['username']?>/"},"headline":"<?php echo $display['title'] ?>","datePublished":"<?php $old_published_time=$display["date_time"];
$published_time=date("Y-m-d",strtotime($old_published_time));echo $published_time?>T<?php $old_published_time=$display["date_time"];$published_time=date("H:i:s+00:00",strtotime($old_published_time));echo $published_time?>","dateModified":"<?php $old_published_time=$display["date_time"];
$published_time=date("Y-m-d",strtotime($old_published_time));echo $published_time?>T<?php $old_published_time=$display["date_time"];$published_time=date("H:i:s+00:00",strtotime($old_published_time));echo $published_time?>","mainEntityOfPage":{"@id":"<?php echo main_url.$_SERVER['REQUEST_URI'];?>"},"wordCount":54,"commentCount":0,"publisher":{"@id":"<?= main_url ?>/#organization"},"image":{"@id":"<?php echo main_url.$_SERVER['REQUEST_URI'];?>#primaryimage"},"thumbnailUrl":"<?= main_url ?>/files/images/articles/<?php echo $display['id'] ?>_mini.jpg<?= website_version ?>","keywords":["<?php echo $display['keywords'] ?>"],"articleSection":["<?= $categories['title'] ?>"],"inLanguage":"he-IL","potentialAction":[{"@type":"CommentAction","name":"Comment","target":["<?php echo main_url.$_SERVER['REQUEST_URI'];?>#respond"]}],"copyrightYear":"<?php $old_published_time=$display["date_time"];
$published_time=date("Y",strtotime($old_published_time));echo $published_time?>","copyrightHolder":{"@id":"<?= main_url ?>/#organization"}},{"@type":"WebPage","@id":"<?php echo main_url.$_SERVER['REQUEST_URI'];?>","url":"<?php echo main_url.$_SERVER['REQUEST_URI'];?>","name":"<?php echo $display['title'] ?>","isPartOf":{"@id":"<?= main_url ?>/#website"},"primaryImageOfPage":{"@id":"<?php echo main_url.$_SERVER['REQUEST_URI'];?>#primaryimage"},"image":{"@id":"<?php echo main_url.$_SERVER['REQUEST_URI'];?>#primaryimage"},"thumbnailUrl":"<?= main_url ?>/files/images/articles/<?php echo $display['id'] ?>_mini.jpg<?= website_version ?>","datePublished":"<?php $old_published_time=$display["date_time"];
$published_time=date("Y-m-d",strtotime($old_published_time));echo $published_time?>T<?php $old_published_time=$display["date_time"];$published_time=date("H:i:s+00:00",strtotime($old_published_time));echo $published_time?>","dateModified":"<?php $old_published_time=$display["date_time"];
$published_time=date("Y-m-d",strtotime($old_published_time));echo $published_time?>T<?php $old_published_time=$display["date_time"];$published_time=date("H:i:s+00:00",strtotime($old_published_time));echo $published_time?>","description":"<?php echo $display['subtitle'] ?>","breadcrumb":{"@id":"<?php echo main_url.$_SERVER['REQUEST_URI'];?>#breadcrumb"},"inLanguage":"he-IL","potentialAction":[{"@type":"ReadAction","target":["<?php echo main_url.$_SERVER['REQUEST_URI'];?>"]}]},{"@type":"ImageObject","inLanguage":"he-IL","@id":"<?php echo main_url.$_SERVER['REQUEST_URI'];?>#primaryimage","url":"<?= main_url ?>/files/images/articles/<?php echo $display['id'] ?>_mini.jpg<?= website_version ?>","contentUrl":"<?= main_url ?>/files/images/articles/<?php echo $display['id'] ?>_mini.jpg<?= website_version ?>","width":800,"height":420,"caption":"<?= website_name ?>"},{"@type":"BreadcrumbList","@id":"<?php echo main_url.$_SERVER['REQUEST_URI'];?>#breadcrumb","itemListElement":[{"@type":"ListItem","position":1,"name":"Home","item":"<?= main_url ?>/"},{"@type":"ListItem","position":2,"name":"<?php echo $display['title'] ?>"}]},{"@type":"WebSite","@id":"<?= main_url ?>/#website","url":"<?= main_url ?>/","name":"<?= website_name ?>","description":"<?php $query="SELECT * FROM system_options WHERE id=2";$result=mysqli_query($connect_db,$query);while($row=mysqli_fetch_array($result)){echo ''.$row["display"].'';}?>","publisher":{"@id":"<?= main_url ?>/#organization"},"inLanguage":"he-IL"},{"@type":"Organization","@id":"<?= main_url ?>/#organization","name":"<?= website_name ?>","url":"<?= main_url ?>/","logo":{"@type":"ImageObject","inLanguage":"he-IL","@id":"<?= main_url ?>/#/schema/logo/image/","url":"https://news.studio-adi.net/files/images/sharing.jpg<?= website_version ?>","contentUrl":"https://news.studio-adi.net/files/images/sharing.jpg<?= website_version ?>","width":1200,"height":630,"caption":"<?= website_name ?>"},"image":{"@id":"<?= main_url ?>/#/schema/logo/image/"},"sameAs":["https://t.me/studio_adi_news"]},{"@type":"Person","@id":"<?= main_url ?>/author/<?php echo $author['username']?>/","name":"<?php echo $author['firstname'];?> <?php echo $author['lastname'];?>","image":{"@type":"ImageObject","inLanguage":"he-IL","@id":"https://news.studio-adi.net/#/schema/person/image/","url":"<?= main_url ?>/files/images/users/<?php echo $author['avatar']; ?>.webp<?= website_version ?>","contentUrl":"<?= main_url ?>/files/images/users/<?php echo $author['avatar']; ?>.webp<?= website_version ?>","caption":"<?php echo $author['firstname'];?> <?php echo $author['lastname'];?>"}}]}
</script>
</head>
<body>
<?php require_once 'categories.php';?>
<main>
<article>
<div class="articleText">
<!--פרסומת תחילת כתבה-->
<div class="ads_wrap"><ins class="adsbygoogle smartphone_ads_top" style="display:block" data-ad-client="ca-pub-6900847292401850" data-ad-slot="2197512915" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script></div>
<header><hr>
<div class="div_tree_link_to_pages">
<a href="/" class="a_tree_link_to_pages">דף הבית</a> › <a href="/category/<?= $categories['username'] ?>/" class="a_tree_link_to_pages"><?= $categories['title'] ?></a> › <?php echo $display['title'] ?></div><hr>
<div class="div_comprehensive_article_details">
<time datetime="<?php echo $display['date_time']?>"><span class="icon_publication_date_article" title="תאריך פרסום: <?php echo ''.$new_date.''?>"></span> <?php echo ''.$new_date.''?></time> / 
<span class="icon_reading_time_article" title="זמן קריאה: <?php echo $display['reading_time']?> דקות"></span> <?php echo $display['reading_time']?> דקות /
<span class="icon_views_article" title="צפיות: <?php require_once 'views_article.php';$address_id=htmlspecialchars($display['id'],ENT_QUOTES,'UTF-8');addViewToDatabase($address_id); ?><?php echo number_format($display['total_views']);?>"></span> <?php echo thousand_format($display['total_views']);?>
</div></header>
<img class="main_image_for_article" src="<?php if($display['thumbnail']==""){echo'/files/images/articles/default_image.webp';}else{echo'/files/images/articles/'.$display['thumbnail'].'';}?>" alt="<?php echo $display['title'] ?>">
<h1 class="article_title"><mark class="mark_text"><?php echo $display['title'] ?></mark></h1>
<p><mark class="mark_text">נכתב על ידי - <a class="a_mark_text" href="/author/<?php echo $author['username']?>/" rel="author"><?php echo $author['firstname'];?> <?php echo $author['lastname'];?></a></mark></p>
<h2 class="main_article_title"><?php echo $display['subtitle'] ?></h2>
<div class="article_content">
<?php $article_data = ''.$display["body"].'';$article_data = str_replace('{{title}}',''.$display["title"].'',$article_data);echo htmlspecialchars_decode(stripslashes($article_data));?>
<b><a class="link_to_external_site" href="https://t.me/studio_adi_news" title="עבור לקבוצת הטלגרם: מאמרים וחדשות צילום - <?= website_name ?>" target="_blank">הכנסו לקבוצת הטלגרם שלנו - שאלות ודיונים בנושאי צילום <svg viewBox="0 0 512 512"><path d="M511.6 36.86l-64 415.1c-1.5 9.734-7.375 18.22-15.97 23.05c-4.844 2.719-10.27 4.097-15.68 4.097c-4.188 0-8.319-.8154-12.29-2.472l-122.6-51.1l-50.86 76.29C226.3 508.5 219.8 512 212.8 512C201.3 512 192 502.7 192 491.2v-96.18c0-7.115 2.372-14.03 6.742-19.64L416 96l-293.7 264.3L19.69 317.5C8.438 312.8 .8125 302.2 .0625 289.1s5.469-23.72 16.06-29.77l448-255.1c10.69-6.109 23.88-5.547 34 1.406S513.5 24.72 511.6 36.86z"/></svg></a></b>
</div>
<div class="div_tag_page_article">קטגוריה: <a class="tag_on_page_of_article" href="/category/<?= $categories['username'] ?>/" rel="tag"><?= $categories['title'] ?></a></div>
<button class="style_button_report" id="js_button_report">מצאתם טעות בכתבה? ספרו לנו</button>
<div id="article_report" class="modal_report"><div class="modal_content_report">
<div class="style_document_report">
<button class="button_close_report" title="סגירה">X</button><br><br>
<p class="p_report">מצאתם טעות בכתבה? ספרו לנו</p>
<form  onsubmit="submitFormreport(); return false;">
<textarea class="style_Lines_report" name="text" id="message" placeholder="מה הטעות שנתקלתם בה?" autocomplete="off" required ></textarea>
<input class="url_article_report" type="url" name="url" id="url" value="<?php echo main_url.$_SERVER['REQUEST_URI'];?>" required disabled>
<button id="js_button_submit_report" class="button_submit_report" type="submit">שליחה  
<svg class="button_submit_report_svg" viewBox="0 0 448 512"><path d="M446.7 98.6l-67.6 318.8c-5.1 22.5-18.4 28.1-37.3 17.5l-103-75.9-49.7 47.8c-5.5 5.5-10.1 10.1-20.7 10.1l7.4-104.9 190.9-172.5c8.3-7.4-1.8-11.5-12.9-4.1L117.8 284 16.2 252.2c-22.1-6.9-22.5-22.1 4.6-32.7L418.2 66.4c18.4-6.9 34.5 4.1 28.5 32.2z"/></svg></button><br>
<div id="my_form_report"><div id="loading_report"></div></div>
</form></div>
</div></div>
<script>
function _(e){return document.getElementById(e)}function submitFormreport(){_("js_button_submit_report").disabled=!0,_("loading_report").innerHTML='<img class="gif_loading" src="/files/images/image_loading.gif" alt="גיף טוען..">';var e=new FormData;e.append("message",_("message").value),e.append("url",_("url").value);var t=new XMLHttpRequest;t.open("POST","/report_article.php"),t.onreadystatechange=function(){4==t.readyState&&200==t.status&&("success"==t.responseText?_("my_form_report").innerHTML='<h2 class="h2_success_submit_report">הדיווח נשלח <svg class="success_submit_report_svg" viewBox="0 0 512 512"><path d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z"/></svg></h2><br>':(_("loading_report").innerHTML=t.responseText,_("js_button_submit_report").disabled=!1))},t.send(e)}var modal_report=document.getElementById("article_report"),btn=document.getElementById("js_button_report"),span=document.getElementsByClassName("button_close_report")[0];btn.onclick=function(){modal_report.style.display="block"},span.onclick=function(){modal_report.style.display="none"};
</script>










<div class="hr_sect">שתפו את המאמר</div>
<?php
$comment_settings['comment_file'] = "comments/$id.dat";
$comment_settings['admin_email'] = "";
$comment_settings['add_comments'] = "bottom";
$comment_settings['comments_per_page'] = 5;
$comment_settings['auto_link'] = true;
$comment_settings['text_maxlength'] = 1500;
$comment_settings['word_maxlength'] = 1500;
$comment_settings['anonym'] = "בעילום שם";
date_default_timezone_set("Asia/Jerusalem");
$comment_settings['time_format'] = "%d.%m.%Y בשעה: %H:%M:%S";
$comment_settings['anker'] = "#comments";
$comment_settings['wordwrap'] = "<br>";
$comment_lang['title'] =              "Comments";
$comment_lang['email_title'] =        "שלח אימייל ל- [name]";
$comment_lang['hp_title'] =           "[homepage] - עבור לאתר";
$comment_lang['no_comments_yet'] =    "-תיהיה הראשון להגיב-";
$comment_lang['comments_shown'] =     "<p style='direction:rtl;'>[comments] מתוך [comments_total] תגובות (דף [part])</p>";
$comment_lang['previous'] =           "חזור דף אחד אחורה";
$comment_lang['next'] =               "עבור דף אחד קדימה";
$comment_lang['show_all'] =           "צפה בכל התגובות";
$comment_lang['no_comments'] =        "No comments";
$comment_lang['one_comment'] =        "1 comment";
$comment_lang['several_comments'] =   "[comments] comments";
$comment_lang['comment_link_title'] = "Read or write comments";
$comment_lang['email_subject'] =      "Comment to [comment_to]";
$comment_lang['email_text'] =         "Comment to [comment_to] by [name]:\n\n[comment]\n\n\nLink to the comment:\n[link]";
$comment_lang['error'] =              "שגיאה:";
$comment_lang['err_text_too_long'] =  "the text is too long ([characters] characters - maximum is [characters_max] characters)";
$comment_lang['err_word_too_long'] =  "the word [word] is too long";

function comment_make_link($string)
 {
  $string = ' ' . $string;
  $string = preg_replace("#(^|[\n ])([\w]+?://.*?[^ \"\n\r\t<]*)#is", "\\1<a href=\"\\2\">\\2</a>", $string);
  $string = preg_replace("#(^|[\n ])((www|ftp)\.[\w\-]+\.[\w\-.\~]+(?:/[^ \"\t\n\r<]*)?)#is", "\\1<a href=\"https://\\2\">\\2</a>", $string);
  $string = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $string);
  $string = substr($string, 1);
  return $string;
 }
function count_comments($comment_id, $text=0)
 {
  global $comment_settings, $comment_lang;
  $data = file($comment_settings['comment_file']);
  $comment_total_entries = count($data);
  $comment_count = 0;
  for ($i = 0; $i < $comment_total_entries; $i++)
  {
   $parts = explode("|", $data[$i]);
   if ($parts[3] == $comment_id) $comment_count++;
  }
  if ($text == 0) return $comment_count;
  else
   {
    if ($comment_count == 0) $count_text = $comment_lang['no_comments'];
    elseif ($comment_count == 1) $count_text = $comment_lang['one_comment'];
    else $count_text = str_replace("[comments]", $comment_count, $comment_lang['several_comments']);
    return $count_text;
   }
 }
if (isset($_GET['comment_id'])) $comment_id = $_GET['comment_id'];
if (isset($_POST['comment_id'])) $comment_id = $_POST['comment_id'];
if (isset($_GET['comment_popup'])) $comment_popup = $_GET['comment_popup'];
if (isset($_POST['comment_popup'])) $comment_popup = $_POST['comment_popup'];
if (empty($comment_popup) && empty($comment_id) && empty($_GET['comment_popup_link'])) $comment_id = basename($_SERVER["PHP_SELF"]);
if (isset($comment_id))
 {
  if (isset($_GET['comment_page'])) $comment_page = $_GET['comment_page']; else $comment_page = 1;
if (isset($_POST['comment_text']) && trim($_POST['comment_text']) != "")
  {
   unset($errors);
   if (strlen($_POST['comment_text']) > $comment_settings['text_maxlength']) { $err_txt_too_lng = str_replace("[characters]", strlen($_POST['comment_text']), $comment_lang['err_text_too_long']); $err_txt_too_lng = str_replace("[characters_max]", $comment_settings['text_maxlength'], $err_txt_too_lng); $errors[] = $err_txt_too_lng; }
   $text_arr = str_replace("\n", " ", $_POST['comment_text']);
   $text_arr = explode(" ",$text_arr); for ($i=0;$i<count($text_arr);$i++) { trim($text_arr[$i]); $laenge = strlen($text_arr[$i]); if ($laenge > $comment_settings['word_maxlength']) { $errors[] = str_replace("[word]", "\"".htmlentities(stripslashes(substr($text_arr[$i],0,$comment_settings['word_maxlength'])))."...\"", $comment_lang['err_word_too_long']); } }
   $data = file($comment_settings['comment_file']);
   $row_count = count($data);
   for ($row = 0; $row < $row_count; $row++)
     {
      $parts = explode("|", $data[$row]);
      if ($parts[3] == $_POST['comment_id'] && urldecode($parts[4]) == trim($_POST['name']) && trim(urldecode($parts[6])) == trim($_POST['comment_text'])) { $double_entry = true; break; }
     }
   if (empty($errors) && empty($double_entry))
    {
      $comment_text = urlencode(trim($_POST['comment_text']));
      $name = urlencode(trim($_POST['name']));
      $email_hp = trim($_POST['email_hp']);
      if (substr($email_hp,0,7) == "https://") $email_hp = substr($email_hp,7);
      $email_hp = urlencode(base64_encode($email_hp));

      $uniqid = uniqid("");
      if ($comment_settings['add_comments'] == "top")
      {
       $data = file($comment_settings['comment_file']);
       $c = count($data);
       $datei = fopen($comment_settings['comment_file'], 'w+');
       flock($datei, 2);
       fwrite($datei, $uniqid."|".time()."|".$_SERVER["REMOTE_ADDR"]."|".$_POST['comment_id']."|".$name."|".$email_hp."|".$comment_text."\n");
       for ($i = 0; $i < $c; $i++) { fwrite($datei, trim($data[$i])."\n"); }
       flock($datei, 3);
       fclose($datei);
      }
      else
      {
       $datei = fopen($comment_settings['comment_file'], "a");
       flock($datei, 2);
       fwrite($datei, $uniqid."|".time()."|".$_SERVER["REMOTE_ADDR"]."|".$_POST['comment_id']."|".$name."|".$email_hp."|".$comment_text."\n");
       flock($datei, 3);
       fclose($datei);
      }
     if (isset($comment_settings['admin_email']) && $comment_settings['admin_email'] !="")
      {
       if (isset($comment_popup)) { $acid1="?comment_id=".$comment_id."&ampcomment_popup=true"; $acid2="&comment_id=".$comment_id."&comment_popup=true"; } else { $acid1 = ""; $acid2 = ""; }
       $sender_name = trim($_POST['name']);
       if ($sender_name=="") $sender_name = $comment_settings['anonym'];
       if (preg_match("/^[^@]+@.+\.\D{2,5}$/", base64_decode(urldecode($email_hp)))) $sender_email = base64_decode(urldecode($email_hp)); else $sender_email = "no@email.xx";
       $comment_subject = str_replace("[comment_to]", $_POST['comment_id'], $comment_lang['email_subject']);
       $comment_email_text = str_replace("[comment_to]",$_POST['comment_id'],$comment_lang['email_text']);
       $comment_email_text = str_replace("[name]",stripslashes($sender_name),$comment_email_text);
       $comment_email_text = str_replace("[comment]",stripslashes($_POST['comment_text']),$comment_email_text);
       $emailbody = str_replace("[link]","https://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'].$acid1.$comment_settings['anker'],$comment_email_text);
       $header= "From: ".stripslashes($sender_name)." <".stripslashes($sender_email).">\n";
       $header .= "X-Mailer: PHP/" . phpversion(). "\n";
       $header .= "X-Sender-IP: ".$_SERVER["REMOTE_ADDR"]."\n";
       $header .= "Content-Type: text/plain";
       @mail($comment_settings['admin_email'], $comment_subject, $emailbody, $header);
      }
     }
   }
  $data = file($comment_settings['comment_file']);
  $comment_total_entries = count($data);
  $comment_count = count_comments($comment_id);
  if (isset($comment_popup))
   {
    ?><?php
   }
  if (isset($_GET['show_comments'])) $show_comments = $_GET['show_comments'];
  if (isset($_POST['show_comments'])) $show_comments = $_POST['show_comments'];
  if (isset($show_comments) && isset($hide_comments)) unset($hide_comments);
  if (empty($hide_comments))
  {
  $comment_k = 0;
  $comment_a = 0;
  for ($i = 0; $i < $comment_total_entries; $i++)
    {
     $parts = explode("|", $data[$i]);
     if ($parts[3] == $comment_id)
      {
       $comment_k++;
       if ($parts[4] != "") $name = htmlentities(stripslashes(urldecode($parts[4]))); else $name = $comment_settings['anonym'];
       if ($parts[5] != "")
        {
         $email_hp = htmlentities(stripslashes(base64_decode(urldecode($parts[5]))));
         if (preg_match("/^[^@]+@.+\.\D{2,5}$/", $email_hp))
             {
              $email_parts = explode("@", $email_hp);
              $email_name = $email_parts[0];
              $email_domain_tld = $email_parts[1];
              $domain_parts = explode(".", $email_domain_tld);
              $email_domain = "";
              for ($x = 0; $x < count($domain_parts)-1; $x++)
               {
                $email_domain .= $domain_parts[$x].".";
               }
              $email_tld = $domain_parts[$x];
              $email_title = str_replace("[name]",$name,$comment_lang['email_title']);
              $name = "<script type=\"text/javascript\">
              <!--
              document.write('<a href=\"mailto:');
              document.write('".$email_name."');
              document.write('@');
              document.write('".$email_domain."');
              document.write('".$email_tld."');
              document.write('\" title=\"".$email_title."\">');
              //-->
              </script>".$name."<script type=\"text/javascript\">
              <!--
              document.write('</a>');
              //-->
              </script>";
             }
          else
          {
           $hp_title = str_replace("[homepage]",$email_hp,$comment_lang['hp_title']);
           if (isset($comment_popup)) $name = '<a href="'.$email_hp.'" title="'.$hp_title.'" target="_blank">'.$name.'</a>';
           else $name = '<a href="'.$email_hp.'" title="'.$hp_title.'" target="_blank">'.$name.'</a>';
          }
        }
       $comment = htmlentities(stripslashes(urldecode($parts[6])));
       if (isset($comment_settings['wordwrap']) && $comment_settings['wordwrap'] != "") $comment = str_replace("\n", $comment_settings['wordwrap'], trim($comment));
       if (isset($comment_settings['auto_link']) && $comment_settings['auto_link']==true) $comment = comment_make_link($comment);
       $zeit = $parts[1];

       if ($comment_settings['add_comments'] == "top")
        {
         if ($comment_page=="show_all" || ($comment_k>($comment_page-1)*$comment_settings['comments_per_page'] && $comment_k<$comment_page*$comment_settings['comments_per_page']+1)) { ?><p style="margin:0px 0px 5px 0px;"><b><?php echo $name; ?>:</b>&nbsp;<?php echo $comment; ?><br><span style="font-size: 10px; color: #808080;">(<?php echo strftime($comment_settings['time_format'], $parts[1]); ?>)</span></p><?php $comment_a++; }
        }
       else
        {
         if ($comment_page=="show_all" || ($comment_k > ( ($comment_count-$comment_settings['comments_per_page']) - ( ($comment_page-1) * $comment_settings['comments_per_page'] ) ) && $comment_k < (($comment_count-$comment_settings['comments_per_page'])-(($comment_page-1)*$comment_settings['comments_per_page']))+($comment_settings['comments_per_page']+1))) { ?><p class="form-style-10" style="text-align:justify;"><b><?php echo $name; ?>:</b>&nbsp;<?php echo $comment; ?><br><span style="font-size: 10px; color: #808080;">פורסם בתאריך: <?php echo strftime($comment_settings['time_format'], $parts[1]); ?></span></p><?php $comment_a++; }
        }
      }
    }

 $comments_shown = str_replace("[comments]", $comment_a, $comment_lang['comments_shown']);
 $comments_shown = str_replace("[comments_total]", $comment_count, $comments_shown);
 $comments_shown = str_replace("[part]", $comment_page, $comments_shown);
 if ($comment_k == 0) echo "<center><p>".$comment_lang['no_comments_yet']."</p></center>";
 if ($comment_settings['comments_per_page'] < $comment_count && $comment_page != "show_all") { ?>
 <center>
<?php echo $comments_shown; ?>

<?php
if ($comment_settings['comments_per_page'] < $comment_count && $comment_page > 1) { ?>
 <a class="next a22" href="<?php echo basename($_SERVER["PHP_SELF"]); ?>/<?= $id ?>?comment_id=<?php echo $comment_id; ?>&amp;comment_page=<?php echo $comment_page-1; if (isset($comment_popup)) echo "&amp;comment_popup=true"; if (isset($show_comments)) echo "&amp;show_comments=true"; echo $comment_settings['anker']; ?>" title="<?php echo $comment_lang['previous']; ?>">&laquo; אחורה</a>
<?php } ?>

<?php
 if ($comment_settings['comments_per_page'] < $comment_count && $comment_page < (($comment_count/$comment_settings['comments_per_page']))) { ?><a class="next a22" href="<?php echo basename($_SERVER["PHP_SELF"]); ?>/<?= $id ?>?comment_id=<?php echo $comment_id; ?>&amp;comment_page=<?php echo $comment_page+1; if (isset($comment_popup)) echo "&amp;comment_popup=true"; if (isset($show_comments)) echo "&amp;show_comments=true"; echo $comment_settings['anker']; ?>" title="<?php echo $comment_lang['next']; ?>">קדימה &raquo;</a>
<?php } ?>
<br><br>
<a class="previous a22" href="<?php echo basename($_SERVER["PHP_SELF"]); ?>/<?= $id ?>?comment_id=<?php echo $comment_id; ?>&amp;comment_page=show_all<?php if (isset($comment_popup)) echo "&amp;comment_popup=true"; if (isset($show_comments)) echo "&amp;show_comments=true"; echo $comment_settings['anker']; ?>" title="<?php echo $comment_lang['show_all']; ?>"><i class="far fa-arrow-alt-circle-down"></i> הצג הכול <i class="far fa-arrow-alt-circle-down"></i></a> 
<?php } if(isset($errors)){ ?>
<p style="color:red; font-weight:bold;"><?php echo $comment_lang['error']; ?></p>
<ul><?php foreach($errors as $f) { ?><li><?php echo $f; ?></li><?php } ?></ul>
<?php } ?></center>
<style>
a.a22 {
  text-decoration: none;
  display: inline-block;
  padding: 8px 16px;
}
a.a22:hover {
  background-color: #ddd;
  color: black;
}
.previous {
  background-color: #f1f1f1;
  color: black;
}
.next {
  background-color: #4CAF50;
  color: white;
}
</style>

<form class="form-style-9" method="post" action="/page/<?= $id ?>">
<ul>
<?php if (isset($comment_popup)) { ?><input type="hidden" name="comment_popup" value="true" /><?php } ?>
<b><i class="fas fa-comment-dots" style="color: #f3a059;font-size: 24px;"></i>&#160;&#160; מה דעתכם על הכתבה?</b><br><br>
<input type="hidden" name="comment_id" value="<?php echo $comment_id; ?>" />
<input type="hidden" name="show_comments" value="true" />
<li><input placeholder="נא להזין שם משתמש" class="field-style field-split align-right" type="text" name="name"/></li>
<li><input placeholder="נא להזין כתובת מייל / אתר" class="field-style field-split align-right" type="text" name="email_hp"/></li>
<li><textarea placeholder="*נא להזין תגובה" class="field-style" name="comment_text" required><?php if (isset($errors) && isset($_POST['comment_text'])) echo htmlentities(stripslashes($_POST['comment_text'])); ?></textarea></li>
<li><input type="submit" value="פרסמ/י"/></li>
<center><p style="color:red;">**אין חובה להקליד שם משתמש ומייל**</p></center>
</ul>
</form>
<style>
.form-style-10{
	max-width: 550px;
	direction:rtl;
	background: #FAFAFA;
	padding: 30px;
	margin: 30px auto;
	box-shadow: 1px 1px 25px rgba(0, 0, 0, 0.35);
	border-radius: 10px;

}

.form-style-9 {
  margin: 0 10% 30px 30%; 
  width: 60%;
  direction:rtl;
  max-width: 800px;
  padding-right: 30px;
  transition: all 0.4s ease;
}
@media only screen and (max-width: 1000px) {
.form-style-9 {
  margin: 0 10% 30px 10%; 
  width: 80%;
  max-width: 800px;
  padding-right: 0px;
}}
@media only screen and (max-width: 768px) {
.form-style-9 {
  margin: 0 5% 30px 5%; 
  width: 90%;
}}

.form-style-9 ul{
	padding:0;
	margin:0;
	list-style:none;
}
.form-style-9 ul li{
	display: block;
	margin-bottom: 10px;
	min-height: 35px;
}
.form-style-9 ul li  .field-style{
	box-sizing: border-box; 
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box; 
	padding: 8px;
	outline: none;
	border: 1.5px solid #B0CFE0;
	-webkit-transition: all 0.30s ease-in-out;
	-moz-transition: all 0.30s ease-in-out;
	-ms-transition: all 0.30s ease-in-out;
	-o-transition: all 0.30s ease-in-out;

}.form-style-9 ul li  .field-style:focus{
	border: 1.5px solid #f3a059;
}
.form-style-9 ul li .field-split{
	width: 100%;
}
.form-style-9 ul li .field-full{
	width: 100%;
}

.form-style-9 ul li textarea{
	width: 100%;
	height: 100px;
}
.form-style-9 ul li input[type="button"], 
.form-style-9 ul li input[type="submit"] {
  -moz-box-shadow: inset 0px 1px 0px 0px #3985B1;
  -webkit-box-shadow: inset 0px 1px 0px 0px #3985B1;
  box-shadow: inset 0px 1px 0px 0px #3985B1;
  background-color: #216288;
  border: 1px solid #17445E;
  display: inline-block;
  cursor: pointer;
  color: #FFFFFF;
  padding: 8px 18px;
  text-decoration: none;
  outline: none;
  border: none;
  font: 12px Arial, Helvetica, sans-serif;
}
.form-style-9 ul li input[type="button"]:hover, 
.form-style-9 ul li input[type="submit"]:hover {
	background: linear-gradient(to bottom, #2D77A2 5%, #337DA8 100%);
	background-color: #28739E;
}

/*עיצוב לכותרת כתבות מומלצות*/
.design_for_recommended_titles{
  padding: 7px 12px 4px;
  background-color: #5383d3;
  width: 160px;
  margin: 0px;
  font-size: 20px; 
  color: white;
}
@media only screen and (max-width: 450px) {
.design_for_recommended_titles {
  padding: 6px 10px 3px;
  width: 150px;
  font-size: 18px; 
}}
/*סוף*/
</style>

<?php } else { ?>
<p>[ <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>/<?= $id ?>?show_comments=true<?php echo $comment_settings['anker']; ?>" title="<?php echo $comment_lang['comment_link_title']; ?>"><?php echo count_comments($comment_id, 1); ?></a> ]</p>
<?php }
if (isset($comment_popup)){ ?><?php }}
if (isset($_GET['comment_popup_link'])){ ?><?php } ?>
<div class="hr_sect">שתפו את המאמר</div>
<style>

button{
  outline: none;
  cursor: pointer;
  font-weight: 500;
  border-radius: 4px;margin: 0px;
  border: 2px solid transparent;
  transition: background 0.1s linear, border-color 0.1s linear, color 0.1s linear;
}
.view-modal{
  color: #f79321;
  font-size: 18px;
  padding: 10px 25px;
  background: #fff;
}
.popup{
  background: #fff;
  padding: 25px;
  border-radius: 15px;
  top: -150%;
  max-width: 380px;
  width: 100%;
  opacity: 0;
  pointer-events: none;
  box-shadow: 0px 10px 15px rgba(0,0,0,0.1);
  transform: translate(-50%, -50%) scale(1.2);
  transition: top 0s 0.2s ease-in-out,
              opacity 0.2s 0s ease-in-out,
              transform 0.2s 0s ease-in-out;
}
.popup.show{
  top: 50%;
  opacity: 1;
  pointer-events: auto;
  transform:translate(-50%, -50%) scale(1);
  transition: top 0s 0s ease-in-out,
              opacity 0.2s 0s ease-in-out,
              transform 0.2s 0s ease-in-out;

}
.popup :is(header, .icons, .field){
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.popup header{
  padding-bottom: 15px;
  border-bottom: 1px solid #ebedf9;
}
header span{
  font-size: 21px;
  font-weight: 600;
}
header .close, .icons button{
  display: flex;
  align-items: center;
  border-radius: 50%;
  justify-content: center;
  transition: all 0.3s ease-in-out;
}
header .close{
  color: #878787;
  font-size: 17px;
  background: #f2f3fb;
  height: 33px;
  width: 33px;
  cursor: pointer;
}
header .close:hover{
  background: #ebedf9;
}
.popup .content{
  margin: 20px 0;
}
.popup .icons{
  margin: 15px 0 20px 0;
}
.content p{
  font-size: 16px;
}
.content .icons button{
  height: 50px;
  width: 50px;
  font-size: 20px;
  text-decoration: none;
  border: 1px solid transparent;background: #fff;
}
.icons button i{
  transition: transform 0.3s ease-in-out;
}
.icons button:nth-child(1){
  color: #1877F2;
  border-color: #b7d4fb;
}
.icons button:nth-child(1):hover{
  background: #1877F2;
}
.icons button:nth-child(2){
  color: #46C1F6;
  border-color: #b6e7fc;
}
.icons button:nth-child(2):hover{
  background: #46C1F6;
}
.icons button:nth-child(3){
  color: #e1306c;
  border-color: #f5bccf;
}
.icons button:nth-child(3):hover{
  background: #e1306c;
}
.icons button:nth-child(4){
  color: #25D366;
  border-color: #bef4d2;
}
.icons button:nth-child(4):hover{
  background: #25D366;
}
.icons button:nth-child(5){
  color: #0088cc;
  border-color: #b3e6ff;
}
.icons button:nth-child(5):hover{
  background: #0088cc;
}
.icons button:hover{
  color: #fff;
  border-color: transparent;
}
.icons button:hover i{
  transform: scale(1.2);
}
.content .field{
  margin: 12px 0 -5px 0;
  height: 45px;
  border-radius: 4px;
  padding: 0 5px;
  border: 1px solid #e1e1e1;
}
.field.active{
  border-color: #f79321;
}
.field i{
  width: 50px;
  font-size: 18px;
  text-align: center;
}
.field.active i{
  color: #f79321;
}
.field input{
  width: 100%;
  height: 100%;
  border: none;
  outline: none;
  font-size: 15px;
}
.field button{
  color: #fff;
  padding: 5px 18px;
  background: #f79321;
}
.field button:hover{
  background: #f68709;
}
</style>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css"/>
  <button class="view-modal">שיתוף</button>
  <div class="popup">
    <header>
      <span>שתפו לחברים!</span>
      <div class="close"><i class="fa-solid fa-xmark"></i></div>
    </header>
    <div class="content">
      <p>שיתוף באמצעות:</p>
      <ul class="icons">
<button onclick="return facebook_click(700, 600)" title="שיתוף בפייסבוק">
<i class="fab fa-facebook-f"></i></button>
<button onclick="return twitter_click(700, 600)" title="שיתוף בטוויטר">
<i class="fab fa-twitter"></i>
</button>
<button class="article_sharing_button" onclick="return whatsapp_click(700, 600)" title="שיתוף בווצאפ"><i class="fab fa-instagram"></i></button>
<button class="article_sharing_button" onclick="return whatsapp_click(700, 600)" title="שיתוף בווצאפ"><i class="fab fa-whatsapp"></i></button>

<button onclick="return telegram_click(700, 600)" title="שיתוף בטלגרם"><i class="fab fa-telegram-plane"></i></button>
      </ul>
      <p>או העתיקו קישור</p>
      <div class="field">
        <i class="fa-solid fa-link"></i>
        <input type="text" readonly value="<?php echo main_url.$_SERVER['REQUEST_URI'];?>">
        <button>העתקה</button>
      </div>
    </div>
  </div>

  <script>
    const viewBtn = document.querySelector(".view-modal"),
    popup = document.querySelector(".popup"),
    close = popup.querySelector(".close"),
    field = popup.querySelector(".field"),
    input = field.querySelector("input"),
    copy = field.querySelector("button");

    viewBtn.onclick = ()=>{
      popup.classList.toggle("show");
    }
    close.onclick = ()=>{
      viewBtn.click();
    }

    copy.onclick = ()=>{
      input.select(); //select input value
      if(document.execCommand("copy")){ //if the selected text copy
        field.classList.add("active");
        copy.innerText = "הועתק";
        setTimeout(()=>{
          window.getSelection().removeAllRanges(); //remove selection from document
          field.classList.remove("active");
          copy.innerText = "העתק";
        }, 3000);
      }
    }
  </script>

<div class="sharing_section">
<button class="article_sharing_button" onclick="return twitter_click(700, 600)" title="שיתוף בטוויטר">
<svg class="social_article_svg" viewBox="0 0 512 512"><path d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"/></svg></button>
<button class="article_sharing_button" onclick="return facebook_click(700, 600)" title="שיתוף בפייסבוק">
<svg class="social_article_svg" viewBox="0 0 320 512"><path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/></svg></button>
<button class="article_sharing_button" onclick="return whatsapp_click(700, 600)" title="שיתוף בווצאפ">
<svg class="social_article_svg" viewBox="0 0 448 512"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg></button>
<button class="article_sharing_button" onclick="return friends_click(700, 600)" title="שיתוף בפרנדס">
<svg class="social_article_svg" viewBox="0 0 448 512"><path d="M448 127.1C448 181 405 223.1 352 223.1C326.1 223.1 302.6 213.8 285.4 197.1L191.3 244.1C191.8 248 191.1 251.1 191.1 256C191.1 260 191.8 263.1 191.3 267.9L285.4 314.9C302.6 298.2 326.1 288 352 288C405 288 448 330.1 448 384C448 437 405 480 352 480C298.1 480 256 437 256 384C256 379.1 256.2 376 256.7 372.1L162.6 325.1C145.4 341.8 121.9 352 96 352C42.98 352 0 309 0 256C0 202.1 42.98 160 96 160C121.9 160 145.4 170.2 162.6 186.9L256.7 139.9C256.2 135.1 256 132 256 128C256 74.98 298.1 32 352 32C405 32 448 74.98 448 128L448 127.1zM95.1 287.1C113.7 287.1 127.1 273.7 127.1 255.1C127.1 238.3 113.7 223.1 95.1 223.1C78.33 223.1 63.1 238.3 63.1 255.1C63.1 273.7 78.33 287.1 95.1 287.1zM352 95.1C334.3 95.1 320 110.3 320 127.1C320 145.7 334.3 159.1 352 159.1C369.7 159.1 384 145.7 384 127.1C384 110.3 369.7 95.1 352 95.1zM352 416C369.7 416 384 401.7 384 384C384 366.3 369.7 352 352 352C334.3 352 320 366.3 320 384C320 401.7 334.3 416 352 416z"/></svg></button>
<button class="article_sharing_button" onclick="return telegram_click(700, 600)" title="שיתוף בטלגרם">
<svg class="social_article_svg" viewBox="0 0 496 512"><path d="M248,8C111.033,8,0,119.033,0,256S111.033,504,248,504,496,392.967,496,256,384.967,8,248,8ZM362.952,176.66c-3.732,39.215-19.881,134.378-28.1,178.3-3.476,18.584-10.322,24.816-16.948,25.425-14.4,1.326-25.338-9.517-39.287-18.661-21.827-14.308-34.158-23.215-55.346-37.177-24.485-16.135-8.612-25,5.342-39.5,3.652-3.793,67.107-61.51,68.335-66.746.153-.655.3-3.1-1.154-4.384s-3.59-.849-5.135-.5q-3.283.746-104.608,69.142-14.845,10.194-26.894,9.934c-8.855-.191-25.888-5.006-38.551-9.123-15.531-5.048-27.875-7.717-26.8-16.291q.84-6.7,18.45-13.7,108.446-47.248,144.628-62.3c68.872-28.647,83.183-33.623,92.511-33.789,2.052-.034,6.639.474,9.61,2.885a10.452,10.452,0,0,1,3.53,6.716A43.765,43.765,0,0,1,362.952,176.66Z"/></svg></button>
</div>
<div class="hr_sect">תהנו 🤩</div>
<div class="next_prev_article">
<?php $query="SELECT * FROM articles WHERE approved='1' AND status='3' AND id=".$id."-1";$result=mysqli_query($connect_db,$query);if(mysqli_num_rows($result)<=0){echo '<a class="next_prev_article_a next_prev_article_disabled" href="#" rel="nofollow">&#171; למאמר הקודם</a>';}else{while($prev_article=mysqli_fetch_assoc($result)){$resultsa[]='<a class="next_prev_article_a" href="/page/'.$prev_article["id"].'/" title="'.$prev_article["title"].'">&#171; למאמר הקודם</a>';}echo implode($resultsa);}$query="SELECT * FROM articles WHERE approved='1' AND status='3' LIMIT 1 OFFSET ".$id."";$result=mysqli_query($connect_db,$query);if(mysqli_num_rows($result)<=0){echo '<a class="next_prev_article_a next_prev_article_disabled" href="#" rel="nofollow">למאמר הבא &#187;</a>';}else{while($next_article=mysqli_fetch_assoc($result)){$results[]='<a class="next_prev_article_a" href="/page/'.$next_article["id"].'/" title="'.$next_article["title"].'">למאמר הבא &#187;</a>';}echo implode($results);} ?>
</div>
<!--פרסומת סוף טקסט כתבה-->
<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6900847292401850" data-ad-slot="6872622071" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</div>
</article>
<div class="ads_float ads_left_article">
<div class="ads_text_div"><p class="ads_text_p">פרסומת</p></div><div class="ads_float_position">
<ins class="adsbygoogle" style="display:inline-block;width:100%;height:600px" data-ad-client="ca-pub-6900847292401850" data-ad-slot="9720574864"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</div></div>
<div class="ads_float ads_right_article">
<div class="ads_text_div"><p class="ads_text_p">פרסומת</p></div><div class="ads_float_position">
<ins class="adsbygoogle" style="display:inline-block;width:100%;height:600px" data-ad-client="ca-pub-6900847292401850" data-ad-slot="1642198891"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</div></div>
<div class="offers_div">
<input class="offers_radio" id="offers_one" name="group" type="radio" checked>
<input class="offers_radio" id="offers_two" name="group" type="radio">
<input class="offers_radio" id="offers_three" name="group" type="radio">
<div class="div_offers_label">
<label class="offers_label" id="offers_one_tab" for="offers_one">מומלצים</label>
<label class="offers_label" id="offers_two_tab" for="offers_two">הפופולריים ביותר</label>
<label class="offers_label" id="offers_three_tab" for="offers_three">הועלו לאחרונה</label>
</div>
<div class="offers_panels">
<div class="offers_panels_fadein" id="offers_one_panel">
<?php $time_today = date('Y-m-d H:i:s');$sql = "SELECT a.* FROM articles a JOIN users u ON a.author_id = u.author_id WHERE a.date_time < ? AND a.approved = '1' AND a.status = '3' AND u.account_suspended = 0 AND u.status = '3' ORDER BY RAND() LIMIT 6";$stmt = $connect_db->prepare($sql);$stmt->bind_param("s", $time_today);$stmt->execute();$result = $stmt->get_result();if(mysqli_num_rows($result)>0){while ($row = $result->fetch_assoc())
{$old_date="".$row["date_time"]."";$new_date=date("d.m.Y",strtotime($old_date));$query="SELECT * FROM categories WHERE id=".$row["category_id"]."";$number_result=mysqli_query($connect_db,$query);$tag_name=mysqli_fetch_assoc($number_result);$query="SELECT * FROM users WHERE author_id=".$row["author_id"]."";$number_result=mysqli_query($connect_db,$query);$author_name=mysqli_fetch_assoc($number_result); echo '<article class="article_preview"><header class="header_article_preview"><time datetime="'.$row["date_time"].'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$new_date.'"></span> '.$new_date.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: '.$row["reading_time"].' דקות"></span> '.$row["reading_time"].' דקות / <span class="icon_author_article" title="נכתב על ידי: '.$author_name["firstname"].' '.$author_name["lastname"].'"></span> <a href="/author/'.$author_name["username"].'/">'.$author_name["firstname"].' '.$author_name["lastname"].'</a> / <span class="icon_views_article" title="צפיות: '.number_format(''.$row["total_views"].'').'"></span> <span class="total_views_format">'.thousand_format(''.$row["total_views"].'').'</span> / <span class="icon_tag_article" title="קטגוריה: '.$tag_name["title"].'"></span> <a href="/category/'.$tag_name["username"].'/">'.$tag_name["title"].'</a></header><a href="/page/'.$row["id"].'/"><img loading="lazy" src="/files/images/articles/'.$row["id"].'_mini.webp'.website_version.'" alt="'.$row["title"].'"></a><h2><a href="/page/'.$row["id"].'/">'.$row["title"].'</a></h2><p>'.$row["subtitle"].'</p></article>';}}
else{$time_today_example_article=date('Y-m-d H:i:s');$time_today_special_format_example_article=date('d.m.Y');echo '<article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article><article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article><article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article><article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article><article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article><article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article>';}?>
<!--פרסומת סוף כתבה-->
<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6900847292401850" data-ad-slot="5550472559" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</div>
<div class="offers_panels_fadein" id="offers_two_panel">
<?php $time_today = date('Y-m-d H:i:s');$sql = "SELECT a.* FROM articles a JOIN users u ON a.author_id = u.author_id WHERE a.date_time < ? AND a.approved = '1' AND a.status = '3' AND u.account_suspended = 0 AND u.status = '3' ORDER BY total_views DESC LIMIT 6";$stmt = $connect_db->prepare($sql);$stmt->bind_param("s", $time_today);$stmt->execute();$result = $stmt->get_result();if(mysqli_num_rows($result)>0){while ($row = $result->fetch_assoc())
{$old_date="".$row["date_time"]."";$new_date=date("d.m.Y",strtotime($old_date));$query="SELECT * FROM categories WHERE id=".$row["category_id"]."";$number_result=mysqli_query($connect_db,$query);$tag_name=mysqli_fetch_assoc($number_result);$query="SELECT * FROM users WHERE author_id=".$row["author_id"]."";$number_result=mysqli_query($connect_db,$query);$author_name=mysqli_fetch_assoc($number_result); echo '<article class="article_preview"><header class="header_article_preview"><time datetime="'.$row["date_time"].'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$new_date.'"></span> '.$new_date.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: '.$row["reading_time"].' דקות"></span> '.$row["reading_time"].' דקות / <span class="icon_author_article" title="נכתב על ידי: '.$author_name["firstname"].' '.$author_name["lastname"].'"></span> <a href="/author/'.$author_name["username"].'/">'.$author_name["firstname"].' '.$author_name["lastname"].'</a> / <span class="icon_views_article" title="צפיות: '.number_format(''.$row["total_views"].'').'"></span> <span class="total_views_format">'.thousand_format(''.$row["total_views"].'').'</span> / <span class="icon_tag_article" title="קטגוריה: '.$tag_name["title"].'"></span> <a href="/category/'.$tag_name["username"].'/">'.$tag_name["title"].'</a></header><a href="/page/'.$row["id"].'/"><img loading="lazy" src="/files/images/articles/'.$row["id"].'_mini.webp'.website_version.'" alt="'.$row["title"].'"></a><h2><a href="/page/'.$row["id"].'/">'.$row["title"].'</a></h2><p>'.$row["subtitle"].'</p></article>';}}
else{$time_today_example_article=date('Y-m-d H:i:s');$time_today_special_format_example_article=date('d.m.Y');echo '<article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article><article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article><article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article><article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article><article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article><article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article>';}?>
</div>
<div class="offers_panels_fadein" id="offers_three_panel">
<?php $time_today = date('Y-m-d H:i:s');$sql = "SELECT a.* FROM articles a JOIN users u ON a.author_id = u.author_id WHERE a.date_time < ? AND a.approved = '1' AND a.status = '3' AND u.account_suspended = 0 AND u.status = '3' ORDER BY id DESC LIMIT 6";$stmt = $connect_db->prepare($sql);$stmt->bind_param("s", $time_today);$stmt->execute();$result = $stmt->get_result();if(mysqli_num_rows($result)>0){while ($row = $result->fetch_assoc())
{$old_date="".$row["date_time"]."";$new_date=date("d.m.Y",strtotime($old_date));$query="SELECT * FROM categories WHERE id=".$row["category_id"]."";$number_result=mysqli_query($connect_db,$query);$tag_name=mysqli_fetch_assoc($number_result);$query="SELECT * FROM users WHERE author_id=".$row["author_id"]."";$number_result=mysqli_query($connect_db,$query);$author_name=mysqli_fetch_assoc($number_result); echo '<article class="article_preview"><header class="header_article_preview"><time datetime="'.$row["date_time"].'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$new_date.'"></span> '.$new_date.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: '.$row["reading_time"].' דקות"></span> '.$row["reading_time"].' דקות / <span class="icon_author_article" title="נכתב על ידי: '.$author_name["firstname"].' '.$author_name["lastname"].'"></span> <a href="/author/'.$author_name["username"].'/">'.$author_name["firstname"].' '.$author_name["lastname"].'</a> / <span class="icon_views_article" title="צפיות: '.number_format(''.$row["total_views"].'').'"></span> <span class="total_views_format">'.thousand_format(''.$row["total_views"].'').'</span> / <span class="icon_tag_article" title="קטגוריה: '.$tag_name["title"].'"></span> <a href="/category/'.$tag_name["username"].'/">'.$tag_name["title"].'</a></header><a href="/page/'.$row["id"].'/"><img loading="lazy" src="/files/images/articles/'.$row["id"].'_mini.webp'.website_version.'" alt="'.$row["title"].'"></a><h2><a href="/page/'.$row["id"].'/">'.$row["title"].'</a></h2><p>'.$row["subtitle"].'</p></article>';}}
else{$time_today_example_article=date('Y-m-d H:i:s');$time_today_special_format_example_article=date('d.m.Y');echo '<article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article><article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article><article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article><article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article><article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article><article class="article_preview"><header class="header_article_preview"><time datetime="'.$time_today_example_article.'"><span class="icon_publication_date_article" title="תאריך פרסום: '.$time_today_special_format_example_article.'"></span> '.$time_today_special_format_example_article.'</time> / <span class="icon_reading_time_article" title="זמן קריאה: 0 דקות"></span> 0 דקות / <span class="icon_author_article" title="נכתב על ידי: המערכת"></span> <a href="">מערכת</a> / <span class="icon_views_article" title="צפיות: 0"></span> <span class="total_views_format">0</span> / <span class="icon_tag_article" title="קטגוריה ברירת מחדל"></span> <a href="">קטגוריה ברירת מחדל</a></header><a href=""><img src="/files/images/articles/default_image.webp'.website_version.'" width="800" height="420" alt="תמונת ברירת מחדל"></a><h2><a href="">כותרת מאמר לדוגמא</a></h2><p>תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא תיאור מאמר לדוגמא.</p></article>';}?>
</div></div></div></main>
<script>
function facebook_click(width, height) {
    var leftPosition, topPosition;
    leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
    topPosition = (window.screen.height / 2) - ((height / 2) + 50);
    var windowFeatures = "status=no,height=" + height + ",width=" + width + ",resizable=no,left=" + leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY=" + topPosition + ",toolbar=no,menubar=no,scrollbars=no,location=no,directories=no";
    u=encodeURIComponent(location.href);
    t=encodeURIComponent(document.title);
    var quote = encodeURIComponent("<?php echo html_entity_decode($display['title'], ENT_QUOTES); ?>\n<?php echo html_entity_decode($display['subtitle'], ENT_QUOTES); ?>\n");
    window.open('http://www.facebook.com/sharer.php?u=' + u + '&quote=' + quote + '&t=' + t, 'sharer', windowFeatures);
    return false;
}
function whatsapp_click(width, height) {
  var leftPosition, topPosition;
  leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
  topPosition = (window.screen.height / 2) - ((height / 2) + 50);
  var windowFeatures = "status=no,height=" + height + ",width=" + width + ",resizable=no,left=" + leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY=" + topPosition + ",toolbar=no,menubar=no,scrollbars=no,location=no,directories=no";
  var url = encodeURIComponent(location.href);
  var title = encodeURIComponent("<?php echo html_entity_decode($display['title'], ENT_QUOTES); ?>");
  var subtitle = encodeURIComponent("<?php echo html_entity_decode($display['subtitle'], ENT_QUOTES); ?>");
  var text = '*' + title + '*%0a' + subtitle + '%0a' + url;
  window.open('https://api.whatsapp.com/send?text=' + text, '_blank', windowFeatures);
  return false;
}
function twitter_click(width, height) {
    var leftPosition, topPosition;
    leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
    topPosition = (window.screen.height / 2) - ((height / 2) + 50);
    var windowFeatures = "status=no,height=" + height + ",width=" + width + ",resizable=no,left=" + leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY=" + topPosition + ",toolbar=no,menubar=no,scrollbars=no,location=no,directories=no";
    var u = encodeURIComponent(location.href);
    var t = encodeURIComponent(document.title);
    var category = encodeURIComponent("<?php echo $categories['username'] ?>");
    var title = encodeURIComponent("<?php echo html_entity_decode($display['title'], ENT_QUOTES); ?>\n");
    window.open('https://twitter.com/intent/tweet?url=' + u + '&hashtags=' + category + '&via=studio_adi_news&text=' + title + '&t=' + t, 'sharer', windowFeatures);
    return false;
}

function friends_click(width, height) {
var leftPosition, topPosition;
leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
topPosition = (window.screen.height / 2) - ((height / 2) + 50);
var windowFeatures = "status=no,height=" + height + ",width=" + width + ",resizable=no,left=" + leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY=" + topPosition + ",toolbar=no,menubar=no,scrollbars=no,location=no,directories=no";
u=location.href;
t=document.title;
window.open('https://friends.studio-adi.net/share?url=<?php echo $display['title'] ?>%0a<?php echo $display['subtitle'] ?>%0a%23<?= $categories['title'] ?>%0a'+encodeURIComponent(u),'sharer', windowFeatures);
return false;}
function telegram_click(width, height) {
   var leftPosition, topPosition;
   leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
   topPosition = (window.screen.height / 2) - ((height / 2) + 50);
   var windowFeatures = "status=no,height=" + height + ",width=" + width + ",resizable=no,left=" + leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY=" + topPosition + ",toolbar=no,menubar=no,scrollbars=no,location=no,directories=no";
   var url = encodeURIComponent(location.href);
   var title = encodeURIComponent("**" + "<?php echo html_entity_decode($display['title'], ENT_QUOTES); ?>" + "**");
   var subtitle = encodeURIComponent("<?php echo html_entity_decode($display['subtitle'], ENT_QUOTES); ?>");
   var text = '' + title + '%0a' + subtitle + '%0a' + url;
   window.open('https://telegram.me/share/url?url=' + text, '_blank', windowFeatures);
   return false;
}
</script>
<?php require_once 'footer.php';?>
</body>
</html>
