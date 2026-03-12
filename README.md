# 🚀 GoldByte CAMS (Clinic Appointment & Management System)

GoldByte CAMS is a robust, full-stack healthcare management platform designed to bridge the gap between patients, medical professionals, and administrators. Beyond standard clinical operations, it features a built-in **SaaS-style SMS Marketing Hub**, allowing for personalized patient engagement and revenue-generating bulk communication.

---

## 🏛️ Project Architecture
* **Frontend:** HTML5, CSS3 (Modern UI with Poppins Typography), JavaScript (Vanilla).
* **Backend:** PHP 8.x (Procedural & Object-Oriented paradigms).
* **Database:** MySQL (Relational structure with transactional integrity).
* **Integrations:** Arkesel SMS Gateway (REST API).
* **Security:** Environment variables (.env), Bcrypt password hashing, and role-based access control (RBAC).

---

## ✨ Key Features

### 🏥 Clinical Operations
* **Patient Portal:** Self-registration, appointment booking, and digital access to prescriptions/lab reports.
* **Doctor Workspace:** Managed clinical queue, digital prescription issuance, and lab report uploads.
* **Secure Appointments:** Automated fee calculation for home services and emergency consultations.

### 🚀 SMS Marketing Hub (SaaS Component)
* **Personalized Messaging:** Dynamic `{name}` tag support for high-engagement communication.
* **Audience Targeting:** * Direct database blast to all registered patients.
    * **CSV Upload:** Import external leads for marketing campaigns.
    * **Manual Entry:** Quick personalized messaging using `Name:Number` format.
* **Real-time Reporting:** Decodes gateway responses to track successful deliveries or failures (DND, Invalid Number).

### 🛡️ Security & Integrity
* **Transactional SQL:** Uses `BEGIN TRANSACTION` and `ROLLBACK` to ensure no data loss during multi-step processes.
* **Sanitization:** Complete protection against SQL Injection using `real_escape_string`.
* **Environment Safety:** Sensitive API keys and database credentials stored in a protected `.env` file.

---

## 🛠️ Installation & Setup (Local Environment)

1.  **Clone the Repository:**
    Place the project folder in your XAMPP `htdocs` directory.
    ```bash
    C:\xampp\htdocs\cams
    ```

2.  **Database Configuration:**
    * Import the provided `database.sql` file via phpMyAdmin.
    * Ensure the `users`, `appointments`, `doctors`, and `specialties` tables are created.

3.  **Environment Variables:**
    Create a `.env` file in the root directory:
    ```env
    DB_HOST=localhost
    DB_NAME=cams_db
    DB_USER=root
    DB_PASS=
    ARKESEL_API_KEY=your_arkesel_key_here
    ```

4.  **Folder Permissions:**
    Ensure the `uploads/medical/` and `includes/` folders are readable/writable by the server.

---

## 📂 Project Structure
```text
cams/
├── .env                # Sensitive credentials
├── includes/
│   ├── db_connect.php  # Central DB connection
│   ├── header.php      # Dynamic Navigation (RBAC)
│   ├── sms_helper.php  # Arkesel API transmission hub
│   └── footer.php      # Global footer
├── uploads/            # Medical lab reports & CVs
├── admin_dashboard.php # Clinical operations management
├── bulk_sms.php        # Marketing campaign hub
├── prescribe.php       # Doctor consultation workspace
└── process_patient.php # Transactional registration logic





Developed by: TEAM GOLDBYTE