<?php

require_once 'config.php';
require_once '../SSI.php';

if (isset($config['RedirectFrom'])) {
    if ($_SERVER['HTTP_HOST'] === $config['RedirectFrom']) {
        header('Location: http://' . $config['RedirectTo'] . $_SERVER['REQUEST_URI']);
        die();
    }
}

if ($context['user']['is_guest']) {
    $loggedin = false;
    $rewards = array();
} else {
    $loggedin = true;
    $forum_id = (int)$context['user']['id'];
    $rewards = array();

    try {
        // Connect to DB
        $PDO = new PDO($config['DB']);
        $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Find our ID and Secret Key
        $s = $PDO->prepare('
            SELECT
                    id,
                    secretKey
            FROM
                    user
            WHERE
                    forumId = :forumId;');
        $s->execute(array(':forumId' => $forum_id));
        $rows = $s->fetchAll(PDO::FETCH_ASSOC);
        // Not in DB => No rewards
        if (empty($rows)) {
            $rewards = array();
        } else {
            $user_id = $rows[0]['id'];
            $secret_key = $rows[0]['secretKey'];

            // Find our rewards
            $s = $PDO->prepare('
                SELECT
                        user_flag.id_flag AS id_flag,
                        user_flag.enabled AS enabled,
                        flag.description AS description
                FROM
                        user_flag
                LEFT JOIN
                        flag
                ON
                        user_flag.id_flag = flag.id
                WHERE
                        id_user = :id;');
            $s->execute(array(':id' => $user_id));
            $rewards = array();
            foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $rewards[] = array(
                    'id' => $row['id_flag'],
                    'enabled' => (bool)$row['enabled'],
                    'description' => $row['description'],
                    'field_name' => $row['id_flag'] . '_enabled'
                );
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                foreach ($rewards as &$reward) {
                    $newValue = (array_key_exists($reward['field_name'], $_POST));
                    if ($newValue !== $reward['enabled']) {
                        $s = $PDO->prepare('
                            UPDATE
                                    user_flag
                            SET
                                    enabled = :enabled
                            WHERE
                                    id_user = :id
                                    AND id_flag = :flag;');
                        $s->execute(array(':id' => $user_id,
                                    ':flag' => $reward['id'],
                                    ':enabled' => $newValue));
                        $reward['enabled'] = $newValue;
                    }
                }
            }
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

require_once 'template.php';

?>
