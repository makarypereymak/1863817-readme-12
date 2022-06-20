<?php
require 'util/helpers.php';
require 'util/mysql.php';

session_start();
isSessionExist();
$con = connect();

$userId = $_SESSION['userId'];
$posts = doQueryForType($userId);

if (count($posts) > 9) {
  $linkForMorePages = 1;
  $dataForTemplate['linkForMorePage'] = $linkForMorePages;
}

$pageAmount = ceil(count($posts) / 6);

if (isset($_GET['sort']) && !empty($_GET['sort'])) {
  $sort = $_GET['sort'];

  if ($sort === 'likes') {
    usort($posts, function ($a, $b) {
      return ($a['likes_amount'] < $b['likes_amount']);
    });
  } else if ($sort === 'data') {
    usort($posts, function ($a, $b) {
      return (strtotime($a['post_date']) < strtotime($b['post_date']));
    });
  } else if ($sort === 'popular' || (!isset($_GET['post']) && !isset($sort))) {
    usort($posts, function ($a, $b) {
      return ($a['number_of_views'] < $b['number_of_views']);
    });
  }
}

if (isset($_GET['page']) && !empty($_GET['page'])) {
  $pageNumber = $_GET['page'];

  if (!isset($posts[$pageNumber * 6 + 1])) {
    $noMorePages = 1;
    $dataForTemplate['noMorePages'] = $noMorePages;
  }
}

if (isset($linkForMorePages) && !empty($linkForMorePages)) {
  if ($pageNumber === 1) {
    $posts = array_slice($posts, 0, 6);
  } else {
    $posts = array_slice($posts, $pageNumber * 6 - 6, 6);
  }
}

if (isset($posts) && !empty($posts)) {
  $dataForTemplate['cards'] = $posts;
}

if (isset($dataForTemplate)) {
  $page_content = include_template('popular-template.php', $dataForTemplate);
} else {
  $page_content = include_template('popular-template.php');
}

$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'readme: популярное', 'avatar' => getAvatarForUser($_SESSION['username'])]);

if (isset($layout_content) && !empty($layout_content)) {
  print($layout_content);
}
