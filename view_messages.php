<!-- create table messages (
    id int(6) unsigned auto_increment primary key,
     subject varchar(100) not null,
    message text not null,
    created_at timestamp default current_timestamp
); -->

<?php
include '../Database/db.php';
$sql = "SELECT * FROM messages ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$messages = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Messages</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .message-item:hover {
            background-color: #f9fafb;
        }
        .message-item h2 {
            color: #1d4ed8;
        }
        .message-item p {
            line-height: 1.6;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-extrabold text-center text-blue-600 mb-6">Messages</h1>
        <div class="bg-white shadow-lg rounded-lg p-6">
            <?php if (!empty($messages)): ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($messages as $message): ?>
                        <li class="py-4 message-item">
                            <h2 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($message['subject']); ?></h2>
                            <p class="text-gray-700 mb-2"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                            <span class="text-sm text-gray-500"><?php echo htmlspecialchars($message['created_at']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-center text-gray-500">No messages found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
