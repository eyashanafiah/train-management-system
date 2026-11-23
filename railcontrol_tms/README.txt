RAILCONTROL – INTEGRATED TRAIN MANAGEMENT SYSTEM
Starter build for XAMPP / PHP / MySQL

INSTALLATION
1. Extract the railway_tms folder into C:\xampp\htdocs\
2. Open XAMPP Control Panel. Start Apache and MySQL.
3. Open http://localhost/phpmyadmin/
4. Choose Import, select database.sql from this folder, and click Import.
5. Open config/database.php. The default XAMPP settings are localhost, user root, blank password.
6. Visit http://localhost/railcontrol_tms/
7. Sign in with:
   Username: admin
   Password: password

IMPORTANT
- Change the demo password after initial testing.
- This is an academic development starter, not a certified railway safety/control system.
- Signal controls are simulations only and must never be connected to real railway equipment.
- Tracking uses sample database coordinates; live GPS/API integration is a later feature.
- For production use, add CSRF protection, stricter role permissions, validation, backups, HTTPS and secure server configuration.

FIRST TEST CHECKLIST
[ ] Apache and MySQL are running
[ ] Database imported without errors
[ ] Login works
[ ] Dashboard shows sample counts
[ ] Add/edit/delete a train
[ ] Add/edit a station
[ ] Create a route using existing station IDs
[ ] Create a schedule using existing train and route IDs
[ ] Signal simulation updates
[ ] Dispatch logs record system actions

DATABASE RELATIONSHIPS
stations 1—many routes (origin/destination)
trains 1—many schedules
routes 1—many schedules
trains 1—many train_positions
stations 1—many train_positions
trains/stations 1—many maintenance_issues
users 1—many dispatch_logs

NOTE ON ROUTES AND SCHEDULES
For the first build, route and schedule forms accept numeric station/train/route IDs.
Use the seeded IDs initially, then we can improve these forms with dropdowns populated from MySQL in the next development pass.
