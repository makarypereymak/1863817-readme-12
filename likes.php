<?php
require 'util/mysql.php';

session_start();
$con = connect();

if (!isset($_SESSION['username'])) {
  header('Location: /login.php');
}

$idPost = $_GET['postId'];
$userId = $_SESSION['userId'];

$result = doQuery($con, "SELECT * FROM posts WHERE id_post = $idPost");

if ($result) {
  if ($_GET['amilike'] === 'no') {
    $insertLike = mysqli_query($con, "INSERT INTO likes (id_post, id_user, likes_date) VALUE ($idPost, $userId, NOW())");
  } else {
    $insertLike = mysqli_query($con, "DELETE FROM likes WHERE id_post = $idPost AND id_user = $userId");
  }

  $location = 'Location: ' . $_SERVER['HTTP_REFERER'];
  header($location);
}