// Define global functions first
function renderScheduledMessages(messages = []) {
    console.log('Rendering messages:', messages);
    const messagesList = document.getElementById('messagesList');

    if (!messages || messages.length === 0) {
        messagesList.innerHTML = '<div class="no-messages">No scheduled messages</div>';
        return;
    }

    messagesList.innerHTML = '';

    messages.forEach(message => {
        const messageElement = document.createElement('div');

        // Set color based on sent status
        const isSent = message.sent;
        messageElement.className = `message-item ${isSent ? 'sent' : 'pending'}`;

        // Format the date/time for display
        const scheduledDateTime = new Date(`${message.scheduled_date}T${message.scheduled_time}`);
        const formattedDateTime = scheduledDateTime.toLocaleString();
        const now = new Date();
        const isOverdue = scheduledDateTime < now && !isSent;

        // Status indicator
        const statusText = isSent ? 'Sent' : (isOverdue ? 'Overdue' : 'Pending');
        const statusClass = isSent ? 'status-sent' : (isOverdue ? 'status-overdue' : 'status-pending');

        messageElement.innerHTML = `
            <div class="message-actions">
                <div class="message-status ${statusClass}">${statusText}</div>
                <button class="delete-btn" onclick="deleteMessageFromServer('${message.id}')">Delete</button>
            </div>
            <div class="message-details">
                <strong>${message.name}</strong> - ${message.email} ${message.phone ? '| ' + message.phone : ''}
            </div>
            <div class="message-content">${message.message}</div>
            <div class="message-date-time">Scheduled for: ${formattedDateTime}</div>
            <div class="message-created">Created: ${message.created_at}</div>
        `;
        messagesList.appendChild(messageElement);
    });
}

function formatDateTime(date, time) {
    const dateTime = new Date(`${date}T${time}`);
    return dateTime.toLocaleString();
}

// DOM ready event
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('messageForm');
    const messagesList = document.getElementById('messagesList');

    // Load scheduled messages from server
    loadScheduledMessagesFromServer();

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        console.log('Form submitted');
        const formData = new FormData(form);
        const messageData = {
            name: formData.get('name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            date: formData.get('date'),
            time: formData.get('time'),
            message: formData.get('message')
        };

        console.log('Message data:', messageData);

        // Save to server
        const result = await saveMessageToServer(messageData);

        console.log('Save result:', result);

        if (result.success) {
            // Reset form
            form.reset();
            alert('Message scheduled successfully!');
        } else {
            alert('Error scheduling message: ' + result.message);
        }
    });


    // Expose deleteMessageFromServer to global scope for the onclick handler
    window.deleteMessageFromServer = deleteMessageFromServer;

    // Force reload of CSS to ensure latest changes are applied (fix for caching issues)
    const links = document.getElementsByTagName('link');
    for (let i = 0; i < links.length; i++) {
        if (links[i].rel === 'stylesheet') {
            links[i].href = links[i].href.split('?')[0] + '?v=' + new Date().getTime();
        }
    }
});

// Fetch scheduled messages from server on load and periodically
loadScheduledMessagesFromServer();
setInterval(loadScheduledMessagesFromServer, 60000); // Refresh every minute

async function loadScheduledMessagesFromServer() {
    try {
        console.log('Fetching scheduled messages...');
        const response = await fetch('scheduler.php');
        console.log('Response status:', response.status);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        console.log('Received data:', result);

        if (result.success) {
            // Store in a global variable for access by other functions
            window.scheduledMessages = result.data;
            renderScheduledMessages(result.data);
        } else {
            console.error('Server returned error:', result.error);
        }
    } catch (error) {
        console.error('Error loading scheduled messages:', error);
        // Show error to user
        const messagesList = document.getElementById('messagesList');
        messagesList.innerHTML = '<div class="error-message">Error loading messages: ' + error.message + '</div>';
    }
}

async function saveMessageToServer(message) {
    try {
        console.log('Sending message to server:', message);
        const response = await fetch('scheduler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(message)
        });

        console.log('Server response status:', response.status);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        console.log('Server response:', result);

        if (result.success) {
            // Reload messages after successful save
            loadScheduledMessagesFromServer();
        }

        return result;
    } catch (error) {
        console.error('Error saving message:', error);
        return { success: false, message: 'Network error: ' + error.message };
    }
}

async function deleteMessageFromServer(id) {
    try {
        const response = await fetch(`scheduler.php?id=${id}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            // Reload messages after successful deletion
            loadScheduledMessagesFromServer();
        }

        return result;
    } catch (error) {
        console.error('Error deleting message:', error);
        return { success: false, message: 'Network error' };
    }
}

function sendScheduledMessage(message) {
    // The actual sending happens on the server side via the cron job
    // This is just for notification purposes
    console.log(`Message scheduled for ${message.name} (${message.email}): ${message.message}`);

    // Show notification
    if ('Notification' in window) {
        if (Notification.permission === 'granted') {
            new Notification('Message Scheduled!', {
                body: `Message scheduled for ${message.name} at ${message.scheduled_date} ${message.scheduled_time}`,
                icon: 'notification-icon.png'
            });
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(function (permission) {
                if (permission === 'granted') {
                    new Notification('Message Scheduled!', {
                        body: `Message scheduled for ${message.name} at ${message.scheduled_date} ${message.scheduled_time}`,
                        icon: 'notification-icon.png'
                    });
                }
            });
        }
    }
}