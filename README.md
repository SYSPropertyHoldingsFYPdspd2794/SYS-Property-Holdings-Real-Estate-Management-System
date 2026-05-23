SYS Property Holdings - Real Estate Management System (O2O)

Introduction
SYS Property Holdings Real Estate Management System is an O2O (Online-to-Offline) platform. It replaces spreadsheet records. The system executes lead acquisition, data verification, and offline contract signing. It excludes payment gateways. Core functions encompass property display, appointment booking, document pre-checks, and housing allocation.

Business Flow
1. Discovery: Users fetch property records, calculate loan installments, and insert property IDs into wishlists.
2. Booking & Submission: Users insert showroom appointments or housing applications. Users upload financial documents (Format constraint: PDF, Size constraint: 5MB).
3. Lead Assignment: Administrators fetch pending appointments and assign them to staff members.
4. Offline Action & Status Update: Staff members fetch assigned customer records and documents. Staff members update system statuses (Completed, No-Show, Approved, Rejected).
5. Execution: Administrators execute the randomization algorithm on 'Approved' application records to allocate housing units.

Features by Role

Customer
1. Fetch property catalog with state and type filters.
2. Compute loan installments using system interest rates.
3. Insert showroom appointments with date conflict exclusion.
4. Upload financial documents (PDF, 5MB).

Staff
1. Fetch assigned customer records.
2. Read customer documents.
3. Update showroom visit outcomes and application statuses.

Admin
1. Execute CRUD operations for property records.
2. Assign pending appointments to staff members.
3. Execute the lucky draw algorithm.
4. Render state distribution and staff conversion charts.
5. Export PDF reports.

Technical Highlights
1. Data Destruction (PDPA): The system identifies documents exceeding the retention period. It executes database row deletion and server file removal (unlink).
2. System Parameters: The system_settings table stores variables like interest rates and income limits. Administrators update values without code modification.
3. Audit Trails: The audit_logs table records lead assignments, algorithm executions, and file deletions to track operation history.

Tech Stack
Frontend: HTML, CSS, JavaScript, Bootstrap, Chart.js
Backend: PHP (PDO)
Database: MySQL
Environment: XAMPP (Local Environment), InfinityFree (Testing Environment)