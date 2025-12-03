# Payroll Management System
The right solution in managing the payroll for a company with employees and managers.

## User Stories
- All types of accounts can do the following :
    - Log in to and log out from the system
    - View their profile
    - Modify their own profile details
    - View the profile of others
    - Reset their own password when they forgot. (New)
- Employees and managers can do the following :
    - Submit their time card information
    - Submit their sale receipts 
    - Submit their feedback form
    - View their own payment history
- Managers and admin can do the following :
    - Get notified of their subordinate feedbacks and uploads
    - Validate subordinate uploaded timecards and sales receipts
    - View their subordinate feedbacks
    - Add an available employee / manager to their team
    - Remove their subordinates from their team
- Other than listed above, admin can also do the following :
    - Create a new employee / manager
    - Remove an employee / manager record

## What's New?
- Revamped login and forget password method using Laravel built-in methods.
- Organizing database using Laravel's Eloquent class built-in methods.
- Reorganized the pages view using Laravel's Blade template engine.
- Revamped notifications to managers / admin using Laravel built-in methods.
- Revamped layout design into using Bootstrap default classes, especially in navigation menu.
- Revamped layout design into using Bootstrap default classes, especially in navigation menu.
- Simplify register function to only input registered (new employee) email and desired password.
- Simplify file upload to single file at a time.
- Forget password now send link for employee to reset their password, rather than sending the forgotten password as in the original project.
- Implemented push notifications besides database notifications.
- Contained the project into Docker container.

## How to Run this repository?
- Clone to your IDE.
- Install Docker, then run Docker Compose command **./vendor/bin/sail up --build -d**
- Initialize database by running command **./vendor/bin/sail artisan migrate --seed**
- Run in your browser at localhost. Congratulations! You have entered the application, then login with the following credentials :
    - For admin credentials
    ```
    Email : admin@payroll.com
    Password : 112233
    ```
    - For manager credentials
    ```
    Email : manager@payroll.com
    Password : TestingManager
    ```
    - For employee credentials
    ```
    Email : employee@payroll.com
    Password : TestingStaff
    ```

## References
- Agile Principles, Patterns, and Practices in C# (Payroll Case Study)
- Laravel : Up and Running 3rd edition
- Laravel 12 documentation
- Bootstrap 3 documentation
- Laravel Notification Channel
- Docker documentation
- Udemy course : Junior to Senior Developer ( Docker & Redis chapter )
