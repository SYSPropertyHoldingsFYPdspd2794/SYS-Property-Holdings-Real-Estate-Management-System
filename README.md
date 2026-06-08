<div align="center">
  <h1>🏢 SYS Property Holdings</h1>
  <h3>O2O Real Estate & Affordable Housing Management System</h3>
  
  [![Live Demo](https://img.shields.io/badge/Live_Demo-Online-success?style=for-the-badge&logo=vercel)](https://syspropertyholdings.infinityfreeapp.com/)
  [![PHP](https://img.shields.io/badge/PHP-%3E%3D%207.2-777BB4?style=for-the-badge&logo=php)](https://www.php.net/)
  [![MySQL](https://img.shields.io/badge/MySQL-%3E%3D%208.0-4479A1?style=for-the-badge&logo=mysql)](https://www.mysql.com/)
  [![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap)](https://getbootstrap.com/)
</div>

---

## 📖 1. Project Background (Problem Statement)

The Malaysian real estate sector, especially in managing government-subsidized "Affordable Housing," often struggles with fragmented, manual processes. Agencies and developers frequently rely on decentralized tools like Excel spreadsheets and WhatsApp, resulting in:

1. **Lack of Transparency in Allocation:** Affordable housing units are heavily subsidized, but the manual allocation process is prone to bias, lacking an auditable tracking mechanism.
2. **Hidden Transaction Costs:** First-time buyers are often unaware of hidden fees (e.g., MOT, SPA Legal Fees, Valuation Fees), and frequently overestimate their purchasing power, resulting in high bank loan rejection rates due to a poor **Debt Service Ratio (DSR)**.
3. **Disjointed Lead Management:** Offline showroom appointments and follow-ups are hard to track, causing missed sales opportunities and poor customer service.

## 💡 2. Proposed Solution & O2O Business Workflow

**SYS Property Holdings** addresses these issues through a comprehensive **Online-to-Offline (O2O)** Management System. The platform bridges the gap between digital discovery and physical property handover.

### The Business Workflow:
1. **Online Discovery & Financial Assessment:** Prospects browse properties and use the built-in **PropTech Financial Calculator** to stress-test their DSR and simulate the hidden upfront cash required.
2. **Lead Generation (O2O Booking):** Qualified prospects book physical showroom viewing appointments or submit digital affordable housing applications (uploading payslips/EPF statements).
3. **Offline Verification & Staff CRM:** Regional sales staff receive assigned leads, conduct offline document verification, and update viewing outcomes directly on the platform.
4. **Algorithmic Allocation:** For high-demand affordable units, administrators execute an automated **Canvas-based Allocation Wheel (Lucky Draw)** to randomly and fairly allocate units to approved applicants. 

---

## 🚀 3. Key System Features

### 👤 For Customers (Homebuyers)
* **Property Catalog Engine:** Filter properties dynamically by 16 Malaysian states/regions and specific categories (Standard, Luxury, Commercial, Affordable).
* **PropTech Intelligence & Financial Tools:**
  * *Hidden Costs Bill Calculator:* Accurately estimates SPA legal fees, loan agreements, and stamp duties (MOT) based on local property laws.
  * *DSR Affordability Matrix:* Calculates Debt Service Ratio to evaluate bank loan approval probability before committing to a purchase.
  * *Rent vs. Buy Simulator:* Projects a 30-year financial trajectory comparing renting versus purchasing.
* **O2O Booking & Applications:** Seamlessly book physical showroom appointments or submit robust applications for affordable housing.
* **Document Vault:** Secure server-side mechanism to upload sensitive documents (e.g., EPF statements, payslips) required for affordable housing validation.
* **Status Tracking:** Interactive dashboard for users to track their appointment stages, document verification status, and lucky draw ballot outcomes in real-time.

### 💼 For Staff (Sales & Verification Officers)
* **Regional Lead Management:** Sales staff are assigned leads specific to their geographical coverage area for highly localized service.
* **Compliance Pre-Check:** Secure, read-only interface to review customer financial documents ensuring anti-fraud compliance without allowing document downloads.
* **Showroom CRM Updates:** Track the physical arrival of customers and update statuses dynamically (`Pending`, `Completed`, `No Show`, `Cancelled`).
* **Eligibility Endorsement:** Cross-check the customer's declared income against their uploaded documents to endorse their affordable housing application (`APPROVED_FOR_DRAW` or `REJECTED`).

### 🔑 For Administrators
* **Property & Inventory Management:** Full CRUD (Create, Read, Update, Delete) capability for housing projects. Implements 'Sold Out' status archiving and automatic inventory depletion post-allocation.
* **Centralized Lead Dispatch:** Manually review incoming appointments and assign them to specific regional staff members to balance workload efficiency.
* **Automated Lucky Draw Engine:** An advanced, interactive visual Canvas wheel that algorithmically randomizes and allocates limited affordable housing to the verified applicant pool, instantly synchronizing results to the database.
* **Data Privacy (PDPA) Purge:** Automated protocol to permanently delete sensitive customer financial PDF documents that are older than 7 days, maintaining legal data compliance while preserving text audit logs.
* **Business Intelligence Dashboard:** Generates dynamic, printable Chart.js reports detailing State-Level Pricing trends, User Demographics, and Sales Conversion rates.

---

## 🔐 4. System Architecture & Security Mechanisms

To ensure data integrity and compliance with industry standards, the application implements the following technical safeguards:

* **Session Management & RBAC (Role-Based Access Control):** 
  The system utilizes native PHP sessions (`$_SESSION['role']`) to govern routing. Each restricted directory (`admin/`, `staff/`, `customer/`) executes an `auth_check.php` script prior to rendering HTML. Unauthorized attempts are instantly redirected to the login gateway.
* **SQL Injection Prevention:** 
  All database transactions, specifically CRUD modules and authentication endpoints, process inputs via PHP PDO (PHP Data Objects). The use of Prepared Statements with bounded parameters prevents malicious SQL injections.
* **Cryptographic Hashing:** 
  Customer passwords are never stored in plaintext. Passwords are mathematically hashed during registration using the `password_hash()` function utilizing the `bcrypt` algorithm, and authenticated via `password_verify()`.
* **File Upload Sanitization:** 
  The affordable housing module accepts sensitive financial PDFs. The backend rigidly verifies the MIME type (`application/pdf`) and rejects executable extensions before moving files to the `uploads/` directory.
* **Draw Algorithm Mechanism:**
  The lottery engine reads the array of `APPROVED_FOR_DRAW` users from the database and dynamically divides an HTML5 `<canvas>` arc based on the candidate count. Once the deceleration animation concludes, an asynchronous AJAX request instantly commits the randomized winner to the MySQL database, bypassing frontend manipulation.

---

## 🗄️ 5. Database Schema Overview

The relational database (`sys_property_db`) bridges the O2O logic through several core tables:

* `users`: The centralized identity table. Stores all three roles alongside their bcrypt-hashed passwords, contact numbers, and registered states.
* `properties`: The central catalog. Stores geographical mapping, pricing, and category flags (e.g., `is_affordable`). Includes an inventory counter that depletes upon successful sales.
* `appointments`: The operational bridge table. Links the `customer_id` (foreign key) who booked the viewing, the `property_id` being viewed, and the assigned `staff_id` handling the offline meetup.
* `applications`: Dedicated strictly to the Affordable Housing workflow. Stores the file path referencing the uploaded income document and tracks the step-by-step verification status.

---

## 🛠️ 6. Technology Stack

* **Frontend:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5, Chart.js, SweetAlert2
* **Backend:** PHP (Native / Procedural with PDO extension for security)
* **Database:** MySQL / MariaDB

---

## 📂 7. Directory Structure

```text
SYS-Property-Holdings-Real-Estate-Management-System/
├── admin/                  # Administrator dashboard, lucky draw engine, CRUD modules
├── customer/               # Customer-facing views, property detail, wishlist, applications
├── staff/                  # Staff dashboard, lead verification, showroom appointment CRM
├── includes/               # Reusable components (header, footer, db_connect.php, auth_check)
├── assets/                 # Static assets (CSS, JS, site images, uploaded documents)
├── uploads/                # Secure storage for customer financial PDF uploads
├── NEWEST sys property db.sql # The main MySQL database schema & seed data
├── index.php               # Landing page & property catalog entry point
├── login.php / register.php# Authentication endpoints for all 3 user roles
└── README.md               # Project documentation
```

---

## ⚙️ 8. Installation & Local Deployment Guide

Follow these steps to deploy the system locally using **XAMPP**.

### Prerequisites
* **XAMPP** (with PHP 7.4 or higher)
* Web Browser (Chrome, Edge, or Firefox)

### Step 1: Clone or Place the Repository
Move the project folder into your XAMPP `htdocs` directory:
```text
C:\xampp\htdocs\SYS_Property\SYS-Property-Holdings-Real-Estate-Management-System
```

### Step 2: Database Initialization
1. Start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Open your browser and navigate to `http://localhost/phpmyadmin/`.
3. Create a new database named **`sys_property_db`**.
4. Click the **Import** tab, choose the file `NEWEST sys property db.sql` (located in the project root), and click **Go**.

### Step 3: Database Configuration
The system connects to the database via `includes/db_connect.php`. For a standard XAMPP setup, no password is required. The default configuration is:
```php
$servername = "localhost";
$username   = "root";
$password   = "";                // Default XAMPP has no password
$dbname     = "sys_property_db";
```
*(If your local MySQL has a password, update the `$password` variable accordingly.)*

### Step 4: Launch the Application
Open your browser and enter the following URL:
```text
http://localhost/SYS_Property/SYS-Property-Holdings-Real-Estate-Management-System/index.php
```

---

## 🌐 9. Live Web Hosting (Demo)

The project is also deployed and accessible live via InfinityFree hosting:
🔗 **[https://syspropertyholdings.infinityfreeapp.com/](https://syspropertyholdings.infinityfreeapp.com/)**

---

## 🧪 10. System Test Credentials

To evaluate the system fully, please utilize the following pre-configured test accounts. Passwords are case-sensitive. Note that these accounts are seeded with initial data for immediate demonstration.

| Role | Email Address | Password | Permissions & Testing Focus |
| :--- | :--- | :--- | :--- |
| **Customer** | `testc@gmail.com` | `Password123!` | Access to frontend catalog, testing financial calculators, uploading PDF applications, and booking appointments. |
| **Staff Officer** | `tests@gmail.com` | `Password123!` | Access to the staff portal. Focus on verifying uploaded customer PDFs and updating the status of showroom appointments. |
| **Administrator** | `test@gmail.com` | `Password123!` | Full backend access. Focus on assigning staff to leads, managing property CRUD, executing the Lucky Draw wheel, and viewing BI charts. |

---

## 📖 11. Quick User Guide (How to test the workflow)

To experience the full business lifecycle, follow these specialized testing scenarios:

### Scenario A: Standard Appointment Booking Workflow
1. **Customer:** Login as `testc@gmail.com`. Go to **Properties**, select a "Standard" or "Luxury" property, and click **Book Appointment**. Choose a date and submit.
2. **Admin:** Login as `test@gmail.com`. Navigate to **Leads & Appointments**. Find the new appointment and click **Assign Staff** to allocate it to a sales officer.
3. **Staff:** Login as `tests@gmail.com`. Go to **Assigned Viewings**. Assume the customer arrived at the showroom, and update the status to `Completed`.

### Scenario B: Affordable Housing Application & Allocation Workflow
1. **Customer:** Login as `testc@gmail.com`. Find an "Affordable" property. Instead of booking, submit an **Affordable Housing Application** by uploading a sample PDF document (simulating an income slip).
2. **Staff:** Login as `tests@gmail.com`. Navigate to **Document Verification**. Review the customer's PDF safely. If the income matches the requirements, click to change the status to `APPROVED_FOR_DRAW`.
3. **Admin:** Login as `test@gmail.com`. Go to the **Lucky Draw Allocation** module. Select the specific affordable property. The system will load all `APPROVED_FOR_DRAW` users. Spin the wheel to select the winner!

### Additional Special Functions to Test
* **Customer:** Try the **Financial Planner** tools from the navigation bar. Input a salary of RM5,000 and debts of RM2,000 to see how the system generates a dynamic DSR report. Add properties to your **Wishlist**.
* **Staff:** Go to the dashboard to view your personal KPIs (e.g., number of viewings completed this month).
* **Admin:** Navigate to the **Business Reports** section to view interactive pie and bar charts regarding sales distributions. Try deleting a test property or updating its price to see changes reflected on the frontend.

---

## 👨‍💻 12. Development Team

**Universiti Teknologi Malaysia (UTM) SPACE - Final Year Project**  
*Section 42 - Group 3*

* **Jason Pow Cheng Wang** – Scrum Master / Project Lead
* **Engku Afif Aizat Bin Che Engku Suhaimi** – Quality Assurance / Test Lead
* **Har Kah Jun** – Frontend Programmer
* **Khairunnisa Binti Kamal** – Backend Programmer

**Project Supervisor:** Mr. Muhammad Hafiz Afiq Bin Khalid

---

## ❓ 13. Troubleshooting & FAQ

* **Q: I get a 'Database connection failed' error.**
  *A: Ensure XAMPP MySQL is running, the database is named exactly `sys_property_db`, and you've imported the `.sql` file. Check `includes/db_connect.php` if your local setup uses a password.*
* **Q: Uploading PDF returns a MIME error.**
  *A: The system strictly accepts standard PDF files. Ensure your PHP installation permits file uploads (`file_uploads = On` in `php.ini`) and that your `uploads/` folder is writable.*
* **Q: Mail features (WhatsApp Links) are not redirecting properly.**
  *A: Ensure you are testing on a device with WhatsApp installed or WhatsApp Web logged in, as the system utilizes O2O WhatsApp integration for direct messaging.*

---

## 📜 14. License & Academic Integrity

This project is developed solely for academic purposes as part of the **Final Year Project** at Universiti Teknologi Malaysia (UTM) SPACE. All business logic, algorithms, and code logic are created by the development team. 

---

## 🙌 15. Acknowledgements

We extend our sincere gratitude to:
* **Mr. Muhammad Hafiz Afiq Bin Khalid** for his continuous guidance, technical feedback, and mentorship throughout the development lifecycle.
* **UTM SPACE** for providing the academic framework and standard requirements to ensure this system is industry-ready.
* The open-source community behind **Bootstrap**, **Chart.js**, and **SweetAlert2** for enabling a premium UI/UX experience.