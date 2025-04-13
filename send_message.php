<!-- create table messages (
    id int(6) unsigned auto_increment primary key,
     subject varchar(100) not null,
    message text not null,
    created_at timestamp default current_timestamp
); -->

<?php
include '../Database/db.php';
if (isset($_POST['submit'])) {
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $sql = "INSERT INTO messages (subject, message) VALUES ('$subject', '$message')";
    if (mysqli_query($conn, $sql)) {
        echo "Message sent successfully";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Send a Message - Student Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <form method="post" action="" class="bg-white p-6 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Send a Message</h2>
        <div class="mb-4">
            <label for="subject" class="block text-gray-700 font-medium mb-2">Subject</label>
            <input type="text" id="subject" name="subject" required 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-4">
            <label for="message" class="block text-gray-700 font-medium mb-2">Message</label>
            <textarea name="message" required maxlength="100" 
                      class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        <div>
            <input type="submit" name="submit" value="Send Message" 
                   class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition">
        </div>
    </form>
</body>
</html>

