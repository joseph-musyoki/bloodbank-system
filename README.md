**1. Project Overview**

BloodBank Kenya is a full-stack PHP web application for managing blood
donations, inventory, and hospital requests across all 47 Kenyan
counties. It implements a clean MVC architecture with role-based
authentication for three distinct user types.

**1.1 System Architecture**

The system follows a front-controller pattern --- all HTTP traffic
enters through public/index.php, which bootstraps the router and
dispatches to the appropriate controller. No framework is used; all core
components are hand-built.

+--------------------------------------------------------------------+
| bloodbank/                                                         |
|                                                                    |
| ├── public/ ← Web root (point Apache/Nginx here)                   |
|                                                                    |
| │ ├── index.php ← Front controller                                 |
|                                                                    |
| │ ├── .htaccess ← Rewrite rules                                    |
|                                                                    |
| │ └── assets/css,js/ ← Static assets                               |
|                                                                    |
| ├── app/                                                           |
|                                                                    |
| │ ├── controllers/ ← Auth, Donor, Staff, Hospital                  |
|                                                                    |
| │ ├── models/ ← DonorModel, InventoryModel, etc.                   |
|                                                                    |
| │ ├── middleware/ ← Auth.php, DonorEligibility, BloodCompatibility |
|                                                                    |
| │ └── views/ ← PHP templates per portal                            |
|                                                                    |
| ├── core/ ← Database.php, Router.php                               |
|                                                                    |
| ├── config/database.php ← DB credentials                           |
|                                                                    |
| ├── routes/web.php ← All route definitions                         |
|                                                                    |
| └── database/ ← schema.sql, seed scripts                           |
+--------------------------------------------------------------------+

**1.2 Three User Portals**

  ---------------------------------------------------------------------
  **Portal**       **Role**    **Key Functions**
  ---------------- ----------- ----------------------------------------
  Donor Portal     donor       Dashboard, donation history, eligibility
                               check, appointment booking

  Staff Dashboard  staff       Inventory management, stock alerts,
                               donor management, request processing

  Hospital Portal  hospital    Blood requests, compatibility view,
                               request tracking, live stock view
  ---------------------------------------------------------------------

**2. System Requirements**

  ---------------------------------------------------------------------
  **Requirement**      **Minimum**          **Recommended**
  -------------------- -------------------- ---------------------------
  PHP                  8.0                  8.2+

  MySQL                8.0                  8.0+

  Web Server           Apache / Nginx / PHP Apache with mod_rewrite
                       dev server           

  PHP Extensions       PDO, pdo_mysql,      All above + mbstring, intl
                       session              

  RAM                  512 MB               2 GB+

  Disk                 50 MB                500 MB (with logs)
  ---------------------------------------------------------------------

**3. Quick Start**

**Step 1 --- Clone / place project**

+--------------------------------------------------------------------+
| \# Place the bloodbank/ folder in your web root, e.g.:             |
|                                                                    |
| cp -r bloodbank/ /var/www/html/bloodbank                           |
|                                                                    |
| \# Or run from any directory using PHP\'s built-in server:         |
|                                                                    |
| cd bloodbank/public                                                |
|                                                                    |
| php -S localhost:8000                                              |
+--------------------------------------------------------------------+

**Step 2 --- Create the database**

+--------------------------------------------------------------------+
| mysql -u root -p                                                   |
|                                                                    |
| mysql\> SOURCE /path/to/bloodbank/database/schema.sql;             |
|                                                                    |
| mysql\> exit                                                       |
+--------------------------------------------------------------------+

**Step 3 --- Configure credentials**

Edit config/database.php to match your MySQL setup:

+--------------------------------------------------------------------+
| return \[                                                          |
|                                                                    |
| \'host\' =\> \'localhost\',                                        |
|                                                                    |
| \'dbname\' =\> \'bloodbank_system\',                               |
|                                                                    |
| \'username\' =\> \'root\',                                         |
|                                                                    |
| \'password\' =\> \'your_password\',                                |
|                                                                    |
| \];                                                                |
+--------------------------------------------------------------------+

**Step 4 --- Seed demo users**

Run the seeder to create/reset demo accounts with password: password

+--------------------------------------------------------------------+
| php database/seed_users.php                                        |
|                                                                    |
| \# Output:                                                         |
|                                                                    |
| \# ✓ staff: staff@bloodbank.ke → password: password                |
|                                                                    |
| \# ✓ donor: donor@test.ke → password: password                     |
|                                                                    |
| \# ✓ hospital: hospital@test.ke → password: password               |
+--------------------------------------------------------------------+

**Step 5 --- Configure web server**

For Apache --- enable mod_rewrite and point DocumentRoot to
bloodbank/public/.

+--------------------------------------------------------------------+
| \# /etc/apache2/sites-available/bloodbank.conf                     |
|                                                                    |
| \<VirtualHost \*:80\>                                              |
|                                                                    |
| DocumentRoot /var/www/html/bloodbank/public                        |
|                                                                    |
| \<Directory /var/www/html/bloodbank/public\>                       |
|                                                                    |
| AllowOverride All                                                  |
|                                                                    |
| Require all granted                                                |
|                                                                    |
| \</Directory\>                                                     |
|                                                                    |
| \</VirtualHost\>                                                   |
|                                                                    |
| sudo a2enmod rewrite                                               |
|                                                                    |
| sudo systemctl restart apache2                                     |
+--------------------------------------------------------------------+

For Nginx:

+--------------------------------------------------------------------+
| location / {                                                       |
|                                                                    |
| try_files \$uri \$uri/ /index.php?\$query_string;                  |
|                                                                    |
| }                                                                  |
|                                                                    |
| location \~ \\.php\$ {                                             |
|                                                                    |
| fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;                    |
|                                                                    |
| include fastcgi_params;                                            |
|                                                                    |
| fastcgi_param SCRIPT_FILENAME                                      |
| \$document_root\$fastcgi_script_name;                              |
|                                                                    |
| }                                                                  |
+--------------------------------------------------------------------+

**4. Demo Logins**

Navigate to /login. Click a role card to auto-fill credentials, then
click Sign In.

+--------------------------------------------------------------------+
| **All demo accounts use password: password**                       |
|                                                                    |
| 🩸 Donor → donor@test.ke                                           |
|                                                                    |
| 🔬 Staff → staff@bloodbank.ke                                      |
|                                                                    |
| 🏥 Hospital → hospital@test.ke                                     |
+--------------------------------------------------------------------+

**If logins fail after schema import**

The bcrypt hash in schema.sql may behave differently across PHP
versions. Use either fix:

**Option A --- SQL fix (run in MySQL):**

+---------------------------------------------------------------------+
| USE bloodbank_system;                                               |
|                                                                     |
| UPDATE users                                                        |
|                                                                     |
| SET password_hash =                                                 |
| \'\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi\' |
|                                                                     |
| WHERE email IN                                                      |
| (\'staff@bloodbank.ke\',\'donor@test.ke\',\'hospital@test.ke\');    |
+---------------------------------------------------------------------+

**Option B --- PHP seeder (generates hash on your server, most
reliable):**

  --------------------------------------------------------------------
  php database/seed_users.php

  --------------------------------------------------------------------

**5. Database Schema (bloodbank_system)**

  ----------------------------------------------------------------------
  **Table**       **Purpose**             **Key Columns**
  --------------- ----------------------- ------------------------------
  users           All accounts (shared    id, email, password_hash, role
                  login)                  

  donors          Donor health &          blood_type, weight_kg,
                  eligibility profile     deferral_until, is_eligible

  hospitals       Hospital accounts &     registration, level,
                  verification            is_verified

  appointments    Donor appointment       donor_id, scheduled_at, status
                  scheduling              

  donations       Each recorded donation  donor_id, donation_date,
                  event                   hemoglobin, status

  blood_units     Individual unit in      unit_code, blood_type,
                  inventory               expiry_date, status

  inventory       Stock thresholds per    min_units, critical_units
                  type/component          

  requests        Hospital blood requests hospital_id, blood_type,
                                          urgency, status

  request_units   Units issued for a      request_id, unit_id
                  request                 

  stock_alerts    Generated alert log     alert_type, is_resolved
  ----------------------------------------------------------------------

**Entity Relationship Summary**

users ──\< donors (one user, one donor profile)

users ──\< hospitals (one user, one hospital profile)

donors ──\< donations ──\< blood_units (donation creates unit)

hospitals ──\< requests ──\< request_units ──\< blood_units

**6. Route Reference**

  --------------------------------------------------------------------------------
  **Method**   **Path**                        **Controller → Action** **Role**
  ------------ ------------------------------- ----------------------- -----------
  GET          /login                          AuthController →        Public
                                               showLogin               

  POST         /login                          AuthController → login  Public

  GET          /register                       AuthController →        Public
                                               showRegister            

  POST         /register                       AuthController →        Public
                                               register                

  GET          /logout                         AuthController → logout Any

  GET          /donor/dashboard                DonorController →       donor
                                               dashboard               

  GET          /donor/history                  DonorController →       donor
                                               history                 

  GET          /donor/appointments             DonorController →       donor
                                               appointments            

  GET          /donor/appointments/book        DonorController →       donor
                                               bookAppointment         

  POST         /donor/appointments/book        DonorController →       donor
                                               storeAppointment        

  GET          /donor/profile                  DonorController →       donor
                                               profile                 

  POST         /donor/profile                  DonorController →       donor
                                               updateProfile           

  GET          /staff/dashboard                StaffController →       staff
                                               dashboard               

  GET          /staff/inventory                StaffController →       staff
                                               inventory               

  POST         /staff/inventory/expire         StaffController →       staff
                                               expireUnits             

  GET          /staff/donors                   StaffController →       staff
                                               donors                  

  GET          /staff/donors/:id               StaffController →       staff
                                               donorDetail             

  GET          /staff/donors/:id/donate        StaffController →       staff
                                               recordDonation          

  POST         /staff/donors/:id/donate        StaffController →       staff
                                               storeDonation           

  POST         /staff/donors/:id/defer         StaffController →       staff
                                               deferDonor              

  GET          /staff/requests                 StaffController →       staff
                                               requests                

  GET          /staff/requests/:id             StaffController →       staff
                                               requestDetail           

  POST         /staff/requests/:id/fulfill     StaffController →       staff
                                               fulfillRequest          

  GET          /hospital/dashboard             HospitalController →    hospital
                                               dashboard               

  GET          /hospital/request               HospitalController →    hospital
                                               showRequestForm         

  POST         /hospital/request               HospitalController →    hospital
                                               submitRequest           

  GET          /hospital/requests              HospitalController →    hospital
                                               myRequests              

  GET          /hospital/requests/:id          HospitalController →    hospital
                                               requestDetail           

  POST         /hospital/requests/:id/cancel   HospitalController →    hospital
                                               cancelRequest           

  GET          /hospital/stock                 HospitalController →    hospital
                                               stockView               
  --------------------------------------------------------------------------------

**7. Business Logic**

**7.1 Donor Eligibility (DonorEligibility.php)**

Every potential donation is screened against the Kenya National Blood
Transfusion Service rules:

  ------------------------------------------------------------------------
  **Rule**           **Threshold**    **Notes**
  ------------------ ---------------- ------------------------------------
  Minimum age        16 years         Calculated from date_of_birth

  Maximum age        65 years         

  Minimum weight     50 kg            Checked on registration and profile
                                      update

  Hemoglobin ---     ≥ 12.5 g/dL      Checked at time of donation
  female                              recording

  Hemoglobin ---     ≥ 13.0 g/dL      
  male                                

  Whole blood        56 days (8       Calculated from last completed
  deferral           weeks)           donation

  Platelet deferral  14 days          

  Plasma deferral    28 days          

  Annual limit       4 donations/year Checked against donation count this
                                      year

  Manual deferral    Staff-set date   Overrides all other checks; reason
                                      stored
  ------------------------------------------------------------------------

+--------------------------------------------------------------------+
| **Staff Override**                                                 |
|                                                                    |
| Staff can override eligibility checks when recording a donation by |
| ticking the override checkbox.                                     |
|                                                                    |
| This requires supervisor approval in clinical practice and is      |
| logged against the staff user ID.                                  |
+--------------------------------------------------------------------+

**7.2 Blood Compatibility (BloodCompatibility.php)**

The system uses the full ABO/Rh compatibility matrix for both whole
blood and plasma:

  ---------------------------------------------------------------
  **Recipient**   **Compatible Donors (Whole Blood)**
  --------------- -----------------------------------------------
  A+              A+, A−, O+, O−

  A−              A−, O−

  B+              B+, B−, O+, O−

  B−              B−, O−

  AB+             AB+, AB−, A+, A−, B+, B−, O+, O− (universal
                  recipient)

  AB−             AB−, A−, B−, O−

  O+              O+, O−

  O−              O− only (universal donor)
  ---------------------------------------------------------------

*Plasma compatibility is the reverse --- AB plasma is universal; O
plasma is most restricted.*

**7.3 Inventory Alerts (InventoryModel.php)**

The system generates three categories of alert:

  ----------------------------------------------------------------------
  **Alert       **Trigger**                      **Display**
  Type**                                         
  ------------- -------------------------------- -----------------------
  CRITICAL      Available units ≤ critical       Red banner on staff
                threshold (default 5)            dashboard

  LOW STOCK     Available units ≤ minimum        Amber banner on staff
                threshold (default 10)           dashboard

  EXPIRY        Units expiring within 7 days     Yellow badge on
  WARNING                                        inventory page
  ----------------------------------------------------------------------

**7.4 Blood Unit Shelf Life**

  ---------------------------------------------------------------------
  **Component**     **Shelf       **Storage Temperature**
                    Life**        
  ----------------- ------------- -------------------------------------
  Whole Blood       35 days       2--6 °C

  Packed Cells      42 days       2--6 °C

  Platelets         5 days        20--24 °C (agitated)

  Fresh Frozen      365 days      −18 °C or below
  Plasma                          

  Cryoprecipitate   365 days      −18 °C or below
  ---------------------------------------------------------------------

**7.5 FIFO Issuing**

When fulfilling a hospital request, compatible units are presented
sorted by expiry_date ASC. This ensures oldest units are used first,
minimising wastage. Staff select units manually and can see days
remaining for each unit before issuing.

**8. Authentication & Security**

**8.1 Role-Based Access Control**

Auth::requireRole(\'staff\') is called at the top of every protected
controller action. Attempting to access a route without the correct role
returns HTTP 403.

+--------------------------------------------------------------------+
| // Example --- staff only                                          |
|                                                                    |
| public function inventory(): void {                                |
|                                                                    |
| Auth::requireRole(\'staff\'); // redirects to login if not staff   |
|                                                                    |
| \...                                                               |
|                                                                    |
| }                                                                  |
|                                                                    |
| // Multi-role                                                      |
|                                                                    |
| Auth::requireRole(\'staff\', \'admin\');                           |
+--------------------------------------------------------------------+

**8.2 CSRF Protection**

Every POST form includes a hidden \_token field. Auth::verifyCsrf()
validates it against the session token using hash_equals() to prevent
timing attacks.

**8.3 Password Hashing**

Passwords are hashed with bcrypt (cost 10) via PHP\'s password_hash()
and verified with password_verify().

**8.4 Input Sanitization**

All user input is validated server-side before persistence. Output is
escaped with htmlspecialchars() in every view. PDO prepared statements
are used throughout --- no raw query interpolation.

**8.5 Session Security**

session_regenerate_id(true) is called on every successful login to
prevent session fixation attacks.

**9. File Reference**

  -------------------------------------------------------------------------------------
  **File**                                 **Description**
  ---------------------------------------- --------------------------------------------
  core/Database.php                        PDO singleton --- one connection for the
                                           request lifetime

  core/Router.php                          Regex-based URL router; supports :param
                                           segments

  app/middleware/Auth.php                  Login, logout, role check, CSRF verify,
                                           session regeneration

  app/middleware/DonorEligibility.php      All KNBTS eligibility rules --- age, weight,
                                           Hb, deferral periods

  app/middleware/BloodCompatibility.php    ABO/Rh compatibility matrix for whole blood
                                           and plasma

  app/models/DonorModel.php                Donor CRUD, search, donation history,
                                           deferral management

  app/models/InventoryModel.php            Live stock query, FIFO compatible units,
                                           alert generation, expiry marking

  app/models/DonationModel.php             Records donation, auto-creates blood_unit,
                                           calculates expiry, updates appointment

  app/models/RequestModel.php              Hospital request CRUD, unit fulfillment with
                                           transaction safety

  app/controllers/AuthController.php       Login, register (donor only), logout,
                                           redirect by role

  app/controllers/DonorController.php      Donor portal --- dashboard, history,
                                           appointments, profile

  app/controllers/StaffController.php      Staff portal --- inventory, donors,
                                           donations, request processing

  app/controllers/HospitalController.php   Hospital portal --- request form, my
                                           requests, stock view

  routes/web.php                           All 27 route definitions with method, path,
                                           controller, action

  database/schema.sql                      Full DB schema --- 9 tables + inventory
                                           thresholds + seed data

  database/seed_users.php                  CLI script to create/reset demo user
                                           accounts

  database/seed_fix.sql                    SQL-only password reset for demo accounts

  public/assets/css/main.css               Complete design system --- dark clinical
                                           theme, 1300+ lines

  public/assets/js/main.js                 Flash dismiss, mobile nav, select-all,
                                           status toggle, input validation
  -------------------------------------------------------------------------------------

**10. Troubleshooting**

  ----------------------------------------------------------------------
  **Problem**        **Likely Cause**     **Fix**
  ------------------ -------------------- ------------------------------
  White screen / 500 Database not         Check config/database.php
  error              connected            credentials; ensure
                                          bloodbank_system DB exists

  Login fails for    Wrong bcrypt hash in Run: php
  staff or hospital  seed data            database/seed_users.php

  404 on all pages   mod_rewrite not      Run: sudo a2enmod rewrite and
  except /           enabled or .htaccess set AllowOverride All in
                     not read             Apache config

  \'Donor profile    Staff user has no    Normal --- staff role goes to
  not found\' after  donors record        /staff/dashboard, not donor
  staff login                             pages

  CSRF token         Session expired or   Reload the page and resubmit
  mismatch           form cached          

  Inventory shows no Schema imported but  Re-run schema.sql which seeds
  units              no donations seeded  sample donations and
                                          blood_units

  Hospital cannot    is_verified = 0 on   Run: UPDATE hospitals SET
  submit request     hospital record      is_verified=1 WHERE id=1;

  PHP 8.0 errors     Syntax uses PHP 8.1  Upgrade to PHP 8.1+ or replace
                     features (enums,     match() with switch() where
                     fibers)              needed
  ----------------------------------------------------------------------

*BloodBank Kenya · Built for the Kenya National Blood Transfusion
Service*
