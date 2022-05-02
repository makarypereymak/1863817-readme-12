<?php
/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function db_get_prepare_stmt($link, $sql, $data = [])
{
  $stmt = mysqli_prepare($link, $sql);

  if ($stmt === false) {
    $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
    die($errorMsg);
  }

  if ($data) {
    $types = '';
    $stmt_data = [];

    foreach ($data as $value) {
      $type = 's';

      if (is_int($value)) {
        $type = 'i';
      } else {
        if (is_string($value)) {
          $type = 's';
        } else {
          if (is_double($value)) {
            $type = 'd';
          }
        }
      }

      if ($type) {
        $types .= $type;
        $stmt_data[] = $value;
      }
    }

    $values = array_merge([$stmt, $types], $stmt_data);

    $func = 'mysqli_stmt_bind_param';
    $func(...$values);

    if (mysqli_errno($link) > 0) {
      $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
      die($errorMsg);
    }
  }

  return $stmt;
}

// Устанавливает соединение с БД
function connect () {
  $con =  mysqli_connect("localhost", "root", "","readme");
  mysqli_set_charset($con, "utf8");

  return $con;
}

// Делает запрос к БД и преобразовывает результат в двумерный массив
function doQuery ($conWithDatabase, $sql) {
  $result = mysqli_query($conWithDatabase, $sql);
  $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

  return $rows;
}

// Получение лайков поста
function getLikesForPost ()
{
  $id = $_GET['post-id'];
  $id *= 1;
  $likes = doQuery(connect(), "SELECT * FROM likes WHERE id_post = $id");

  return $likes;
}

// Получение тегов поста
function getTagsForPost ()
{
  $id = $_GET['post-id'];
  $id *= 1;
  $tags = doQuery(connect(), "SELECT * FROM hashtags WHERE id_post = $id");

  return $tags;
}

// Получение комментариев поста
function getCommentsForPost ()
{
  $id = $_GET['post-id'];
  $id *= 1;
  $comments = doQuery(connect(), "SELECT * FROM comments WHERE id_post = $id");

  return $comments;
}

// Делает запрос на показ конкретного поста
function getPostById () {
  $id = $_GET['post-id'];
  $id *= 1;
  $post = doQuery(connect(), "SELECT * FROM posts JOIN users ON posts.id_user = users.id_user AND id_post = $id");

  return $post;
}

// Делает запрос на показ подписчиков по айди
function getSubById ()
{
  $post = getPostById();
  $idUser = $post[0]['id_user'];
  $subscriptions = doQuery(connect(), "SELECT * FROM subscriptions WHERE id_receiver_sub = $idUser");

  return $subscriptions;
}

// Получение всех постов по айди
function getAllPostsPostsById ()
{
  $post = getPostById();
  $idUser = $post[0]['id_user'];
  $posts = doQuery(connect(), "SELECT * FROM posts WHERE id_user = $idUser");

  return $posts;
}

// Делает запрос в зависимости от типа контента
function doQueryForType ()
{
  if ($_GET['post'] === '1') {
    $posts = doQuery(connect(), "SELECT * FROM posts JOIN users ON posts.id_user = users.id_user AND content_type = 'post-quote'");
  }
  if ($_GET['post'] === '2') {
    $posts = doQuery(connect(), "SELECT * FROM posts JOIN users ON posts.id_user = users.id_user AND content_type = 'post-text'");
  }
  if ($_GET['post'] === '3') {
    $posts = doQuery(connect(), "SELECT * FROM posts JOIN users ON posts.id_user = users.id_user AND content_type = 'post-photo'");
  }
  if ($_GET['post'] === '4') {
    $posts = doQuery(connect(), "SELECT * FROM posts JOIN users ON posts.id_user = users.id_user AND content_type = 'post-link'");
  }
  if ($_GET['post'] === '5') {
    $posts = doQuery(connect(), "SELECT * FROM posts JOIN users ON posts.id_user = users.id_user AND content_type = 'post-video'");
  }
  if (!$_GET['post']) {
    $posts = doQuery(connect(), "SELECT * FROM posts JOIN users ON posts.id_user = users.id_user ORDER BY number_of_views ASC");
  }

  return $posts;
}
?>