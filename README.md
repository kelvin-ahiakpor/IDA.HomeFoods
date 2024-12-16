# IDAFÜ Health & Wellness Consulting Platform

## Overview

IDAFÜ is a comprehensive web platform that connects health and wellness consultants with clients seeking expert guidance. The platform facilitates seamless booking of consultations, session management, and progress tracking.

## Features

### For Clients

- Book consultations with qualified health & wellness experts
- View consultant profiles and expertise areas
- Manage appointments and session history
- Access session reports and recommendations
- Rate and review consultations

### For Consultants

- Manage availability and booking schedule
- Track client sessions and progress
- Create detailed session reports
- View performance metrics and ratings
- Communicate with clients securely

### For Administrators

- Comprehensive dashboard with real-time metrics
- Manage consultants and client accounts
- Monitor platform activity and bookings
- Access detailed analytics and reports
- System configuration and maintenance

## Technical Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL
- **Frontend**: HTML5, TailwindCSS, JavaScript
- **Additional Libraries**: Chart.js for analytics

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/yourusername/idafu-platform.git
   ```

2. Configure your web server (Apache/Nginx) to point to the project directory

3. Create a MySQL database and import the schema:

   ```bash
   mysql -u your_username -p your_database_name < db/schema.sql
   ```

4. Configure database connection:

   ```bash
   cp config/config.example.php config/config.php
   # Edit config.php with your database credentials
   ```

5. Set up required permissions:

   ```bash
   chmod 755 -R /path/to/project
   chmod 777 -R /path/to/project/uploads
   ```

## Directory Structure

```plaintext
idafu-platform/
├── actions/         # Form processing and AJAX handlers
├── assets/
│   ├── css
│   ├── images
│   ├── js         
├── config/
├── db/
├── functions/
├── middleware/
├── models/         # Application object-oriented models
├── uploads/
├── utils/
├── vendor/         # PHP composer libraries for credential management
└── view/
    ├── admin/     
    ├── client/    
    └── consultant/
```

## Color Scheme

- Primary Blue: #435F6F
- Gold Accent: #F1E2B1
- Deep Blue: #3E6576
- Alert Red: #EF4444
- Light Red: #F77171
- Royal Blue: #3760BE
- Bright Blue: #186EF7
- Light Gold: #F1E5B1
- Sky Blue: #68A0BC
- Teal: #33AD89
- Navy: #00009F

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Security

- All user passwords are hashed using PHP's password_hash()
- Input validation and sanitization implemented
- CSRF protection on all forms
- Prepared statements for database queries
- Role-based access control

## License

This project is licensed under the MIT License - see the LICENSE.md file for details

## Support

For support, email [support@idafu.com](mailto:support@idafu.com) or create an issue in the repository.

## Acknowledgments

- TailwindCSS for the UI framework
- Chart.js for analytics visualization
- All contributors who have helped shape IDAFÜ

---
© 2024 IDAFÜ. All rights reserved.
