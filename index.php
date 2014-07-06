<?php

require_once 'config.php';
require_once '../SSI.php';

if (isset($config['RedirectFrom'])) {
    if ($_SERVER['HTTP_HOST'] === $config['RedirectFrom']) {
        header('Location: http://' . $config['RedirectTo'] . $_SERVER['REQUEST_URI']);
        die();
    }
}

function connectDB() {
    global $PDO, $config;
    // Connect to DB
    if (isset($config['DBUsername'])) {
        $PDO = new PDO($config['DB'], $config['DBUsername'], $config['DBPassword']);
    } else {
        $PDO = new PDO($config['DB']);
    }
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

// So the template can hide unnecessary details if GG2 logged them in automatically
$gg2_login = isset($_REQUEST['gg2_login']) && $_REQUEST['gg2_login'] === 'yes';

$message = '';

if (isset($_REQUEST['reward_id'], $_REQUEST['reward_key'])) {
    $loggedin = true;
    $user_id = $_REQUEST['reward_id'];
    $secret_key = $_REQUEST['reward_key'];
    try {
        connectDB();
        // Check our ID and Secret Key
        $s = $PDO->prepare('
            SELECT
                    COUNT(*) as count
            FROM
                    user
            WHERE
                    id = :id AND
                    secretKey = :secretKey;');
        $s->execute(array(
            ':id' => $user_id,
            ':secretKey' => $secret_key
        ));
        $row = $s->fetch(PDO::FETCH_ASSOC);
        // No rows matched, ID or key wrong
        if (!$row['count']) {
            $rewards = array();
            $loggedin = false;
            goto done;
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else if (!$context['user']['is_guest']) {
    $loggedin = true;
    $forum_id = (int)$context['user']['id'];
    try {
        connectDB();
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
            goto done;
        } else {
            $user_id = $rows[0]['id'];
            $secret_key = $rows[0]['secretKey'];
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    $loggedin = false;
    $rewards = array();
    goto done;
}

try {
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
        foreach ($rewards as $key => $reward) {
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
                $rewards[$key] = $reward;
                $message .= ($newValue ? 'Enabled' : 'Disabled') . ' ' . $reward['description'] . PHP_EOL;
            }
        }
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

done:
require_once 'template.php';

?>
