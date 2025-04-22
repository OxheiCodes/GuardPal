<?php
$pageTitle = 'Messages - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$conn = getDBConnection();
$currentUserId = $_SESSION['user_id'];

// Get chat rooms
$stmt = $conn->prepare("
    SELECT cr.*, 
           u1.username as user1_name,
           u2.username as user2_name,
           u1.id as user1_id,
           u2.id as user2_id
    FROM chat_rooms cr
    JOIN users u1 ON cr.user1_id = u1.id
    JOIN users u2 ON cr.user2_id = u2.id
    WHERE cr.user1_id = ? OR cr.user2_id = ?
    ORDER BY cr.created_at DESC
");
$stmt->execute([$currentUserId, $currentUserId]);
$chatRooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get messages for selected chat
$selectedChatId = isset($_GET['chat']) ? (int)$_GET['chat'] : null;
$messages = [];
$otherUser = null;

if ($selectedChatId) {
    $stmt = $conn->prepare("
        SELECT m.*, u.username as sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ");
    
    // Find the other user in the chat
    foreach ($chatRooms as $room) {
        if ($room['id'] == $selectedChatId) {
            $otherUser = ($room['user1_id'] == $currentUserId) ? $room['user2_id'] : $room['user1_id'];
            break;
        }
    }
    
    if ($otherUser) {
        $stmt->execute([$currentUserId, $otherUser, $otherUser, $currentUserId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Conversations</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($chatRooms as $room): ?>
                        <?php 
                        $otherUserName = ($room['user1_id'] == $currentUserId) ? $room['user2_name'] : $room['user1_name'];
                        ?>
                        <a href="?chat=<?php echo $room['id']; ?>" 
                           class="list-group-item list-group-item-action chat-list-item <?php echo ($selectedChatId == $room['id']) ? 'active' : ''; ?>">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?php echo htmlspecialchars($otherUserName); ?></h6>
                                <small><?php echo date('M j, Y', strtotime($room['created_at'])); ?></small>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    
                    <?php if (empty($chatRooms)): ?>
                        <div class="list-group-item">
                            <p class="mb-0 text-muted">No conversations yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <?php if ($selectedChatId && $otherUser): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Chat with <?php echo htmlspecialchars($otherUserName); ?></h5>
                    </div>
                    <div class="card-body chat-box" id="chat-box">
                        <?php foreach ($messages as $message): ?>
                            <div class="message <?php echo ($message['sender_id'] == $currentUserId) ? 'sent' : 'received'; ?>">
                                <?php echo htmlspecialchars($message['message']); ?>
                                <div class="small text-muted">
                                    <?php echo date('M j, g:i a', strtotime($message['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer">
                        <form id="message-form" class="d-flex">
                            <input type="hidden" id="receiver-id" value="<?php echo $otherUser; ?>">
                            <input type="text" class="form-control me-2" id="message-input" placeholder="Type a message..." required>
                            <button type="submit" class="btn btn-primary">Send</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <h4>Select a conversation to start chatting</h4>
                        <p class="text-muted">Choose a conversation from the left or start a new one</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../assets/js/chat.js"></script>
<?php require_once 'footer.php'; ?>