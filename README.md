# Patient Management System (PMS)

A modern, web-based patient management system built with PHP, Tailwind CSS, and Ant Design.

## Current Progress

### 1. Authentication System
- [x] User Registration
- [x] User Login
- [x] Secure Password Handling
- [x] Session Management
- [x] Logout Functionality

### 2. Basic Setup
- [x] Database Configuration
- [x] Project Structure
- [x] Basic Routing
- [x] Error Handling
- [x] Security Measures

### 3. Clinic Settings Module
- [x] Basic Information
  - Clinic Name
  - Doctor Name
  - Logo Upload (pending implementation)

- [x] Professional Details
  - Medical License Number
  - Registration Number
  - Tax ID/GST Number
  - Qualifications
  - Specializations
  - Years of Experience

- [x] Contact Information
  - Email
  - Phone
  - Emergency Contact
  - Website
  - Social Media Links (pending implementation)

- [x] Address Details
  - Street Address
  - City
  - State/Province
  - Postal Code
  - Country

- [x] Financial Settings
  - Consultation Fee
  - Follow-up Fee
  - Emergency Fee
  - Currency Settings

- [x] Working Hours
  - Day-wise Configuration
  - Opening Hours
  - Closing Hours
  - Break Hours
  - Holiday Settings

- [x] Appointment Settings
  - Appointment Duration
  - Max Appointments Per Day

- [x] Policies and Terms
  - Prescription Footer Text
  - Terms & Conditions
  - Cancellation Policy

## Project Structure 
pms/
├── auth/
│ ├── login.php
│ ├── register.php
│ └── logout.php
├── config/
│ ├── database.php
│ └── schema.sql
├── includes/
│ ├── header.php
│ └── footer.php
├── settings/
│ └── index.php
├── dashboard.php
└── index.php

## Database Schema

### Users Table
sql
CREATE TABLE users (
id INT PRIMARY KEY AUTO_INCREMENT,
name VARCHAR(100) NOT NULL,
email VARCHAR(100) UNIQUE NOT NULL,
password VARCHAR(255) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

### Clinic Settings Table
sql
CREATE TABLE clinic_settings (
id INT PRIMARY KEY AUTO_INCREMENT,
user_id INT NOT NULL,
clinic_name VARCHAR(200) NOT NULL,
doctor_name VARCHAR(200) NOT NULL,
license_number VARCHAR(100) NOT NULL,
registration_number VARCHAR(100),
tax_id VARCHAR(50),
qualifications TEXT,
specializations TEXT,
experience_years INT,
languages_spoken TEXT,
email VARCHAR(100),
phone VARCHAR(20),
emergency_contact VARCHAR(20),
website VARCHAR(255),
address TEXT,
city VARCHAR(100),
state VARCHAR(100),
postal_code VARCHAR(20),
country VARCHAR(100),
consultation_fee DECIMAL(10,2),
follow_up_fee DECIMAL(10,2),
emergency_fee DECIMAL(10,2),
currency VARCHAR(10) DEFAULT 'USD',
payment_methods JSON,
working_hours JSON,
break_hours JSON,
appointment_duration INT DEFAULT 15,
max_appointments_per_day INT,
logo_path VARCHAR(255),
digital_signature_path VARCHAR(255),
clinic_photos JSON,
social_media JSON,
notification_preferences JSON,
prescription_footer TEXT,
terms_conditions TEXT,
cancellation_policy TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id)
);

## Upcoming Modules

### 1. Patient Management
- [ ] Patient Registration Form
  - Basic Information
  - Medical History
  - Emergency Contacts
  - Insurance Details
  - Document Upload
- [ ] Patient List View
  - Search Functionality
  - Filter Options
  - Quick Actions
- [ ] Patient Profile
  - Medical History
  - Visit History
  - Prescriptions
  - Lab Reports
  - Billing History

### 2. Appointment System
- [ ] Appointment Scheduling
  - Calendar View
  - Time Slot Management
  - Recurring Appointments
  - Block Time Slots
- [ ] Appointment Types
  - Regular Consultation
  - Follow-up
  - Emergency
  - Specialized Services
- [ ] Notifications
  - SMS Reminders
  - Email Confirmations
  - WhatsApp Integration

### 3. Medical Records
- [ ] Electronic Health Records (EHR)
  - Patient History
  - Vital Signs
  - Diagnoses
  - Treatment Plans
- [ ] Prescription Management
  - Digital Prescriptions
  - Medicine Database
  - Dosage Instructions
  - Print Templates
- [ ] Lab Integration
  - Test Orders
  - Result Management
  - Report Generation
  - Digital Sharing

### 4. Billing & Invoicing
- [ ] Invoice Generation
  - Customizable Templates
  - Tax Calculations
  - Discount Management
- [ ] Payment Processing
  - Multiple Payment Methods
  - Payment Status Tracking
  - Receipt Generation
- [ ] Financial Reports
  - Daily Collections
  - Monthly Revenue
  - Outstanding Payments
  - Tax Reports

## Technical Requirements

### Server Requirements
- PHP >= 7.4
- MySQL >= 5.7
- Apache/Nginx Web Server
- mod_rewrite enabled
- PHP Extensions:
  - PDO PHP Extension
  - JSON PHP Extension
  - Fileinfo PHP Extension
  - GD Library
  - OpenSSL PHP Extension
  - Mbstring PHP Extension

### Browser Support
- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)
- Opera (latest 2 versions)

### Development Tools
- Visual Studio Code / PHPStorm
- Git for version control
- Composer for dependency management
- npm for frontend assets

## Installation Guide

1. **System Requirements Check**
bash
php -v
mysql --version
apache2 -v

2. **Clone the Repository**
bash
git clone https://github.com/yourusername/pms.git
cd pms

3. **Database Setup**
Create database
mysql -u root -p
CREATE DATABASE patient_management;
USE patient_management;
Import schema
mysql -u root -p patient_management < config/schema.sql
Database configuration
cp config/database.example.php config/database.php
Edit database credentials
nano config/database.php
Set proper permissions
chmod 755 -R pms/
chmod 777 -R pms/uploads/
chmod 777 -R pms/temp/

6. **Web Server Configuration**
Apache configuration example
<VirtualHost :80>
ServerName pms.local
DocumentRoot /path/to/pms
<Directory /path/to/pms>
AllowOverride All
Require all granted
</Directory>
</VirtualHost>

## Security Features

### Authentication & Authorization
- Secure password hashing using Bcrypt
- Session-based authentication
- Role-based access control
- Password reset functionality
- Login attempt limiting

### Data Protection
- SQL injection prevention
- XSS protection
- CSRF protection
- Input validation
- Output encoding
- Secure file upload handling

### System Security
- SSL/TLS encryption
- Secure session handling
- Error logging
- Regular security updates
- Data backup system

## Best Practices

### Coding Standards
- PSR-4 autoloading
- PSR-12 coding style
- Clean code principles
- SOLID principles
- DRY (Don't Repeat Yourself)

### Development Workflow
- Feature branching
- Code review process
- Testing before deployment
- Documentation updates
- Version control

### Performance Optimization
- Code optimization
- Database indexing
- Caching implementation
- Asset minification
- Image optimization

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

### Contribution Guidelines
- Follow coding standards
- Write meaningful commit messages
- Update documentation
- Add/update tests
- Review existing issues

## Support

### Official Channels
- GitHub Issues
- Email Support: support@example.com
- Documentation Wiki
- Community Forum

### Reporting Issues
- Use issue templates
- Provide reproduction steps
- Include error messages
- Attach screenshots if applicable

## License

This project is licensed under the MIT License. See [LICENSE.md](LICENSE.md) for details.

## Version History

### v0.1.0 (Current)
- Basic Authentication
- Clinic Settings Module
- Initial Release

### Planned Releases
- v0.2.0: Patient Management
- v0.3.0: Appointment System
- v0.4.0: Medical Records
- v1.0.0: Complete System Release

## Roadmap

### Q2 2024
- Patient Management Module
- Appointment System
- Basic Reporting

### Q3 2024
- Medical Records Module
- Billing System
- Advanced Reports

### Q4 2024
- Mobile App Development
- API Integration
- Third-party Integrations

## Acknowledgments

- PHP Community
- Tailwind CSS Team
- Ant Design Team
- All Contributors
- Open Source Community

## Contact

Project Link: https://github.com/yourusername/pms