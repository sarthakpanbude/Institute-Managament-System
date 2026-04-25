# DNA Institute Management System (IMS) - Setup Guide

Welcome to the **DNA Institute Management System (IMS)**. This system is a production-ready, feature-rich platform designed for coaching institutes.

## 🛠️ Requirements
- **Server**: XAMPP / WAMP / MAMP (Apache + MySQL)
- **PHP**: 7.4 or higher
- **Database**: MySQL 5.7+

## 🚀 Quick Setup
1. **Copy Files**: Place the project folder in your local server's root (e.g., `C:/xampp/htdocs/Institute`).
2. **Database Config**: Open `config.php` and update `DB_NAME`, `DB_USER`, `DB_PASS`, and `APP_URL`.
3. **Initialize Database**: Simply visit the application in your browser. The system will automatically create the database `neet_ims` and all required tables.
4. **Admin Registration**: 
   - Go to the login page.
   - Click **"Register as Admin"** to create the first administrator account.
   - For students and parents, use the Admin Panel to enroll them.

## 🔑 Default Features & Modules
### 1. Admin Portal
- **Dashboard**: Real-time stats and performance/revenue charts via Chart.js.
- **Students**: Full lifecycle management (Registration, Invoice Generation, Batch Assignment).
- **Exams**: Create MCQ-based online exams with timers and marks.
- **Fees**: Track payments, record cash transactions, and view pending dues.
- **Attendance**: Daily batch-wise presence tracking.
- **Notifications Center**: Send simulated push notifications (FCM, SMS, WhatsApp).
- **Resource Management**: Upload PDFs, notes, and timetables.

### 2. Student Portal
- **My Progress**: Personal dashboard with score history.
- **Exam Portal**: Attempt live MCQ tests with a digital timer.
- **Study Materials**: Access and download batch-specific resources.
- **Online Payments**: Pay fees via a simulated Razorpay interface.

### 3. Parent Portal
- **Ward Monitoring**: View linked student's performance, attendance, and fee status.

## 📱 PWA Support (Offline Access)
- The system is pre-configured as a **Progressive Web App**.
- Open the site in Chrome (Desktop/Mobile) and click **"Install App"** to add it to your home screen.
- Resources (Notes/Timetables) once viewed will be available **Offline**.

## 🛡️ Security Features
- **CSRF Protection** (Session-based)
- **SQL Injection Prevention** (PDO Prepared Statements)
- **Password Hashing** (Bcrypt)
- **Role-Based Access Control** (RBAC)

## 📄 Note on PDF Generation
Buttons labeled "Download PDF" or "Export" currently trigger the Browser Print dial (`window.print()`) for lightweight, client-side PDF generation. For automated server-side PDF mailing, integration with `dompdf` via Composer is recommended.

---
**Build with ❤️ by DNA Academy Tech Team**
