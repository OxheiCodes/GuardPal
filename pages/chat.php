<?php
$pageTitle = 'Messages - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$conn = getDBConnection();
$currentUserId = $_SESSION['user_id'];

// Handle direct messaging from profile
$requestedUserId = isset($_GET['with']) ? (int)$_GET['with'] : null;
if ($requestedUserId) {
    // Check if chat room exists
    $stmt = $conn->prepare("
        SELECT id FROM chat_rooms 
        WHERE (user1_id = ? AND user2_id = ?) 
           OR (user1_id = ? AND user2_id = ?)
    ");
    $stmt->execute([$currentUserId, $requestedUserId, $requestedUserId, $currentUserId]);
    $chatRoom = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Create chat room if it doesn't exist
    if (!$chatRoom) {
        // First check if there's a connection between users
        $stmt = $conn->prepare("
            SELECT status FROM connections 
            WHERE ((requester_id = ? AND receiver_id = ?) 
                OR (requester_id = ? AND receiver_id = ?))
                AND status = 'accepted'
        ");
        $stmt->execute([$currentUserId, $requestedUserId, $requestedUserId, $currentUserId]);
        $isConnected = $stmt->fetch();
        
        if ($isConnected) {
            $stmt = $conn->prepare("INSERT INTO chat_rooms (user1_id, user2_id) VALUES (?, ?)");
            $stmt->execute([$currentUserId, $requestedUserId]);
            $selectedChatId = $conn->lastInsertId();
        }
    } else {
        $selectedChatId = $chatRoom['id'];
    }
}

// Get chat rooms
$stmt = $conn->prepare("
    SELECT cr.*, 
           u1.username as user1_name,
           u2.username as user2_name,
           u1.id as user1_id,
           u2.id as user2_id,
           u1.full_name as user1_full_name,
           u2.full_name as user2_full_name,
           u1.profile_image as user1_profile_image,
           u2.profile_image as user2_profile_image
    FROM chat_rooms cr
    JOIN users u1 ON cr.user1_id = u1.id
    JOIN users u2 ON cr.user2_id = u2.id
    WHERE cr.user1_id = ? OR cr.user2_id = ?
    ORDER BY cr.created_at DESC
");
$stmt->execute([$currentUserId, $currentUserId]);
$chatRooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get messages for selected chat
$selectedChatId = isset($selectedChatId) ? $selectedChatId : (isset($_GET['chat']) ? (int)$_GET['chat'] : null);
$messages = [];
$otherUser = null;
$otherUserName = null;
$otherUserImage = null;

if ($selectedChatId) {
    // Find the other user in the chat
    foreach ($chatRooms as $room) {
        if ($room['id'] == $selectedChatId) {
            if ($room['user1_id'] == $currentUserId) {
                $otherUser = $room['user2_id'];
                $otherUserName = $room['user2_full_name'] ?: $room['user2_name'];
                $otherUserImage = $room['user2_profile_image'];
            } else {
                $otherUser = $room['user1_id'];
                $otherUserName = $room['user1_full_name'] ?: $room['user1_name'];
                $otherUserImage = $room['user1_profile_image'];
            }
            break;
        }
    }
    
    if ($otherUser) {
        $stmt = $conn->prepare("
            SELECT m.*, u.username as sender_name, u.full_name
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) 
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at ASC
        ");
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
                        if ($room['user1_id'] == $currentUserId) {
                            $chatOtherUserId = $room['user2_id'];
                            $chatOtherUserName = $room['user2_full_name'] ?: $room['user2_name'];
                            $chatOtherUserImage = $room['user2_profile_image'];
                        } else {
                            $chatOtherUserId = $room['user1_id'];
                            $chatOtherUserName = $room['user1_full_name'] ?: $room['user1_name'];
                            $chatOtherUserImage = $room['user1_profile_image'];
                        }
                        ?>
                        <a href="?chat=<?php echo $room['id']; ?>" 
                           class="list-group-item list-group-item-action chat-list-item <?php echo ($selectedChatId == $room['id']) ? 'active' : ''; ?>">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo $chatOtherUserImage ?: 'https://placehold.co/40x40?text=User'; ?>" 
                                     alt="Profile" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($chatOtherUserName); ?></h6>
                                    <small class="text-muted"><?php echo date('M j, Y', strtotime($room['created_at'])); ?></small>
                                </div>
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
                    <div class="card-header d-flex align-items-center">
                        <img src="<?php echo $otherUserImage ?: 'https://placehold.co/40x40?text=User'; ?>" 
                             alt="Profile" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">
                        <h5 class="mb-0"><?php echo htmlspecialchars($otherUserName); ?></h5>
                        <a href="profile.php?id=<?php echo $otherUser; ?>" class="btn btn-sm btn-outline-primary ms-auto">
                            <i class="fas fa-user me-1"></i> View Profile
                        </a>
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
                        <p class="mt-4">You can connect with other security professionals through their profile pages.</p>
                        <a href="connections.php" class="btn btn-primary mt-2">Browse Your Network</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../assets/js/chat.js"></script>
<?php require_once 'footer.php'; ?>