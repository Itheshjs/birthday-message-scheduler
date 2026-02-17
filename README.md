# Birthday Message Scheduler

A web application that allows users to schedule birthday and special occasion messages to be automatically sent via email at specified dates and times. Perfect for remembering important dates and sending personalized greetings automatically.

## Features

- ✉️ Schedule personalized messages with recipient name, email, and phone number
- 📅 Set specific date and time for message delivery
- ⏰ Automatic email sending at scheduled time
- 📋 View all scheduled messages
- 🗑️ Delete scheduled messages
- 📧 SMTP-based email delivery (Gmail, custom servers)
- 🔄 Background scheduler with configurable check intervals

## Technologies Used

- **Frontend:** HTML5, CSS3, JavaScript (ES6)
- **Backend:** PHP 7+
- **Database:** MongoDB (with fallback stub implementation)
- **Email:** PHPMailer 7.0+ with SMTP
- **Dependency Manager:** Composer

## Setup Instructions

### Prerequisites
- **XAMPP** (Apache 2.4+, PHP 7.0+)
- **MongoDB** (4.0+) - Optional if using MongoDBStub.php
- **Composer** (optional but recommended)
- Windows Command Prompt or PowerShell (for running scheduler)

### Installation Steps

1. **Clone or Download the Project**
   ```
   Place the project in: c:\xampp-new\htdocs\BirthdayM
   ```

2. **Verify XAMPP Installation**
   - Ensure Apache and PHP are installed via XAMPP

3. **Install Dependencies (Optional but Recommended)**
   ```bash
   cd c:\xampp-new\htdocs\BirthdayM
   composer install
   ```
   This installs:
   - PHPMailer (for email functionality)
   - MongoDB PHP Driver (for database operations)

4. **Database Configuration**
   - Edit `config.php`:
     ```php
     define('MONGO_HOST', 'localhost');
     define('MONGO_PORT', '27017');
     define('MONGO_DB', 'birthday_scheduler');
     ```
   - If MongoDB is installed, ensure it's running
   - If not using MongoDB, the application can use the built-in MongoDBStub.php

5. **Email Configuration**
   - Edit `config.php` with your SMTP settings:
     ```php
     define('SMTP_HOST', 'smtp.gmail.com');
     define('SMTP_PORT', 587);
     define('SMTP_USER', 'your-email@gmail.com');
     define('SMTP_PASS', 'your-app-password');
     ```
   - For Gmail:
     - Enable 2-Factor Authentication
     - Generate an App Password (not your regular password)
     - Use the 16-character app password in SMTP_PASS

6. **Start XAMPP Services**
   - Start Apache from XAMPP Control Panel
   - (Optional) Start MongoDB if installed

7. **Access the Application**
   - Open browser: `http://localhost/BirthdayM`

## Project File Structure

```
📁 BirthdayM/
├── 📄 index.html                              # Main web interface
├── 📄 config.php                              # Configuration file (EDIT THIS)
├── 📄 scheduler.php                           # API endpoints for message operations
├── 📄 send_email.php                          # Email sending logic
├── 📄 cron_runner.php                         # Checks and sends scheduled messages
├── 📄 simple_scheduler.php                    # Simplified scheduling logic
├── 📄 MongoDBStub.php                         # Fallback DB implementation (if MongoDB unavailable)
├── 📄 SimpleMail.php                          # Fallback email implementation
├── 📄 script.js                               # Frontend JavaScript
├── 📄 styles.css                              # Frontend styling
├── 📄 run_scheduler.bat                       # Windows batch file to run scheduler
├── 📄 run_scheduler.cmd                       # Windows command file to run scheduler
├── 📄 HOW_TO_RUN.txt                          # Quick start guide
├── 📄 README.md                               # This file
├── 📄 composer.json                           # Composer dependencies
├── 📄 birthday_scheduler_scheduled_messages.json  # JSON fallback for messages
├── 📁 vendor/                                 # Composer dependencies (created after composer install)
│   └── phpmailer/                             # PHPMailer library
│   └── mongodb/                               # MongoDB driver (if installed)
└── 📁 test/                                   # Test files
    ├── test.php
    ├── test_email.php
    ├── test_catchup.php
    └── ...
```

## How to Use the Application

### Web Interface
1. Open `http://localhost/BirthdayM` in your browser
2. Enter recipient details:
   - **Name:** Recipient's name
   - **Email:** Valid email address
   - **Phone:** Optional phone number
3. Set the **Date** and **Time** for message delivery
4. Write your **Message** content
5. Click **"Schedule Message"** to save
6. View all scheduled messages in the list below
7. Delete messages by clicking the delete button

### Running the Scheduler

The scheduler checks for messages that need to be sent and sends them automatically.

#### Option 1: Continuous Background Scheduler (Recommended)
Double-click `run_scheduler.bat` to start the continuous scheduler:
```bash
# The batch file runs:
php cron_runner.php
```
- A command window opens and stays open
- The scheduler checks every 60 seconds
- **Keep the window open** for the scheduler to continue running
- Scheduled messages will be sent automatically at their designated time

#### Option 2: Manual Check (One-Time)
Run from Command Prompt:
```bash
cd c:\xampp-new\htdocs\BirthdayM
php cron_runner.php
```
- Checks for messages ONE TIME
- Sends any due messages
- Then exits

#### Option 3: Windows Task Scheduler (Automated)
For automatic message sending without keeping a window open:
1. Open Windows Task Scheduler
2. Create a Basic Task
3. Set trigger: "Repeat every 1 minute, indefinitely"
4. Set action: Run `C:\xampp-new\php\php.exe` with arguments: `C:\xampp-new\htdocs\BirthdayM\cron_runner.php`
5. Save and enable

## API Endpoints

The application provides REST API endpoints via `scheduler.php`:

### POST /scheduler.php
**Schedule a new message**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "date": "2026-03-15",
  "time": "09:00",
  "message": "Happy Birthday!"
}
```

### GET /scheduler.php
**Retrieve all scheduled messages**
Returns array of all scheduled messages

### DELETE /scheduler.php
**Delete a scheduled message**
```json
{
  "id": "message_id_to_delete"
}
```

## Security Considerations

- 🔐 **Use App Passwords:** For Gmail, generate app-specific passwords instead of using your main password
- 🛡️ **Validate Input:** All inputs are validated on both frontend and backend
- 📝 **Sanitize Data:** Email addresses and phone numbers are sanitized before storage
- 🔒 **Secure Database:** Change default MongoDB credentials in production
- 🚫 **Rate Limiting:** Consider implementing rate limiting to prevent abuse
- 🔑 **Credentials in config.php:** Keep `config.php` out of version control
- 🌐 **HTTPS in Production:** Use HTTPS when deploying to production servers

## Configuration Reference

### config.php Main Settings

```php
// Database
define('MONGO_HOST', 'localhost');
define('MONGO_PORT', '27017');
define('MONGO_DB', 'birthday_scheduler');

// Email SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-16-char-app-password');
define('SENDER_EMAIL', 'your-email@gmail.com');
define('SENDER_NAME', 'Birthday Scheduler');

// Scheduler
define('SCHEDULER_INTERVAL', 60); // Check every 60 seconds
```

## File Descriptions

| File | Purpose |
|------|---------|
| `index.html` | Main web interface and form |
| `script.js` | Frontend JavaScript (form submission, message display) |
| `styles.css` | Styling and layout |
| `config.php` | Global configuration (email, database settings) |
| `scheduler.php` | REST API endpoints (POST, GET, DELETE) |
| `send_email.php` | Email sending logic using PHPMailer |
| `cron_runner.php` | Background scheduler that checks and sends messages |
| `simple_scheduler.php` | Simplified scheduling without dependencies |
| `MongoDBStub.php` | Fallback database implementation (uses JSON file) |
| `SimpleMail.php` | Fallback email implementation |
| `run_scheduler.bat` | Windows batch script to run scheduler |
| `birthday_scheduler_scheduled_messages.json` | JSON file for storing messages (fallback) |

## Troubleshooting

### Issue: "Failed to connect to database"
- **Solution:** Ensure MongoDB is running or using MongoDBStub as fallback
- Check MongoDB connection settings in `config.php`

### Issue: "Email not sending"
- **Solution:** 
  - Verify SMTP credentials in `config.php`
  - For Gmail, use an app password (not regular password)
  - Check firewall/antivirus not blocking port 587
  - Review PHP error logs

### Issue: "Scheduler not running"
- **Solution:**
  - Keep `run_scheduler.bat` window open
  - Or set up Windows Task Scheduler for automatic execution
  - Check PHP path in batch file (should be `c:\xampp-new\php\php.exe`)

### Issue: "Messages not scheduled"
- **Solution:**
  - Check browser console for JavaScript errors (F12)
  - Verify `scheduler.php` is accessible: `http://localhost/BirthdayM/scheduler.php`
  - Check PHP error logs in XAMPP

### Issue: "404 error when opening index.html"
- **Solution:**
  - Ensure Apache is running in XAMPP
  - Verify file path: `c:\xampp-new\htdocs\BirthdayM\index.html`
  - Try: `http://localhost/BirthdayM/`

## Customization

### Styling
- Edit `styles.css` to change colors, fonts, and layout
- Customize the header and form appearance

### Email Templates
- Modify the email message in `send_email.php`
- Customize sender name and email formatting

### Extended Features
- Add SMS notifications (integrate with Twilio)
- Implement user authentication and accounts
- Add recurring messages (weekly, monthly, yearly)
- Send calendar reminders instead of just email
- Add message templates and quick actions
- Store messages in cloud databases (AWS, Azure)

### Styling Customization Example
```css
/* Change header color in styles.css */
header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
```

## Testing

Test files are provided in the project:
- `test.php` - Basic functionality tests
- `test_email.php` - Email sending tests
- `test_catchup.php` - Scheduler catchup tests
- `simple_test.php` - Simple scheduler tests

Run tests:
```bash
php test.php
```

## Development Notes

### Fallback Implementation
The application includes fallback implementations:
- **MongoDBStub.php:** If MongoDB is unavailable, messages are stored in JSON
- **SimpleMail.php:** If PHPMailer is unavailable, uses PHP's built-in mail()

### Database Collections
MongoDB automatically creates:
- `scheduled_messages` - Stores all scheduled messages
- Indexes for efficient querying by scheduled_time

### Message States
Messages have states:
- **pending:** Waiting to be sent
- **sent:** Successfully delivered
- **failed:** Failed to send (stored for retry)

## Performance Tips

1. **Database Optimization:**
   - MongoDB indexes messages by `scheduled_time` for fast queries
   - Regularly archive old/sent messages

2. **Scheduler Efficiency:**
   - Adjust `SCHEDULER_INTERVAL` based on frequency needs
   - Use Task Scheduler instead of keeping window open

3. **Email Optimization:**
   - Consider batch sending for multiple messages
   - Use connection pooling for SMTP

## License

This project is open source. Feel free to modify and use as needed.

## Support & Contributing

For issues, questions, or contributions:
1. Review the troubleshooting section above
2. Check the test files for usage examples
3. Review code comments in PHP files
4. Modify fallback implementations if needed

### Common Tasks

**To change message check interval:**
- Edit `config.php`: `define('SCHEDULER_INTERVAL', 60);`

**To use different email provider:**
- Update SMTP settings in `config.php`
- Test with `test_email.php`

**To add validation rules:**
- Modify JavaScript `script.js` for frontend validation
- Modify `scheduler.php` for backend validation

## Contact & Feedback

For questions or suggestions, provide detailed information about:
- What you're trying to do
- Steps to reproduce issues
- Error messages or logs
- XAMPP version and PHP version

---

**Last Updated:** February 2026  
**Version:** 1.0  
**Status:** Active Development